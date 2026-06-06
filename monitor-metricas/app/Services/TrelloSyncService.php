<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Crypto;
use App\DTOs\BoardDTO;
use App\Exceptions\SynchronizationException;
use App\Exceptions\TrelloApiException;
use App\Exceptions\TrelloConnectionException;
use App\Interfaces\ITrelloService;
use App\Interfaces\ITrelloSyncService;
use App\Repositories\SyncLogRepository;
use App\Repositories\TrelloBoardRepository;
use App\Repositories\TrelloCardRepository;
use App\Repositories\TrelloConnectionRepository;
use App\Repositories\TrelloListRepository;
use App\Repositories\TrelloWorkspaceRepository;
use DateTimeImmutable;
use PDO;
use Throwable;

final class TrelloSyncService implements ITrelloSyncService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly Crypto $crypto,
        private readonly ITrelloService $trello,
        private readonly TrelloConnectionRepository $connections,
        private readonly TrelloWorkspaceRepository $workspaces,
        private readonly TrelloBoardRepository $boards,
        private readonly TrelloListRepository $lists,
        private readonly TrelloCardRepository $cards,
        private readonly SyncLogRepository $logs,
    ) {
    }

    public function status(string $userId): array
    {
        $conn = $this->connections->getByUserId($userId);
        if ($conn === null || $conn->status !== 'connected') {
            return ['connected' => false];
        }
        $token = $this->safeDecrypt($conn->tokenEncrypted);
        if ($token === '') {
            return ['connected' => false];
        }
        return [
            'connected' => true,
            'trello_member_id' => $conn->trelloMemberId ?? '',
            'last_sync_at' => $conn->lastSyncAt,
            'status' => $conn->status,
        ];
    }

    public function connect(string $userId, string $token): array
    {
        $token = trim($token);
        if ($token === '') {
            throw new TrelloConnectionException('Token de Trello requerido.');
        }

        try {
            $member = $this->trello->getMember($token);
        } catch (TrelloApiException $e) {
            throw new TrelloConnectionException($e->getMessage());
        }

        $memberId = (string)($member['id'] ?? '');
        if ($memberId === '') {
            throw new TrelloConnectionException('No se pudo obtener el miembro de Trello.');
        }

        $enc = $this->crypto->encrypt($token);
        $this->connections->upsertConnected($userId, $memberId, $enc);

        return ['connected' => true, 'trello_member_id' => $memberId];
    }

    public function disconnect(string $userId): void
    {
        $empty = $this->crypto->encrypt('');
        $this->connections->markDisconnected($userId, $empty);
    }

    public function getMember(string $userId): array
    {
        $token = $this->tokenForUser($userId);
        return $this->trello->getMember($token);
    }

    public function getWorkspaces(string $userId): array
    {
        $token = $this->tokenForUser($userId);
        $items = $this->trello->getWorkspaces($token);
        $out = [];
        foreach ($items as $w) {
            $out[] = [
                'trello_id' => $w->trelloId,
                'name' => $w->name,
                'description' => $w->description,
            ];
        }
        return $out;
    }

    public function getBoards(string $userId, string $workspaceTrelloId): array
    {
        $token = $this->tokenForUser($userId);
        $items = $this->trello->getBoards($token, $workspaceTrelloId);
        $out = [];
        foreach ($items as $b) {
            $out[] = [
                'trello_id' => $b->trelloId,
                'workspace_trello_id' => $b->workspaceTrelloId,
                'name' => $b->name,
                'description' => $b->description,
                'url' => $b->url,
                'closed' => $b->closed,
            ];
        }
        return $out;
    }

    public function getLists(string $userId, string $boardTrelloId): array
    {
        $token = $this->tokenForUser($userId);
        $items = $this->trello->getLists($token, $boardTrelloId);
        $out = [];
        foreach ($items as $l) {
            $out[] = [
                'trello_id' => $l->trelloId,
                'board_trello_id' => $l->boardTrelloId,
                'name' => $l->name,
                'closed' => $l->closed,
            ];
        }
        return $out;
    }

    public function getCards(string $userId, string $boardTrelloId): array
    {
        $token = $this->tokenForUser($userId);
        $items = $this->trello->getCards($token, $boardTrelloId);
        $out = [];
        foreach ($items as $c) {
            $out[] = [
                'trello_id' => $c->trelloId,
                'board_trello_id' => $c->boardTrelloId,
                'list_trello_id' => $c->listTrelloId,
                'name' => $c->name,
                'description' => $c->description,
                'due_date' => $c->dueDateIso,
                'closed' => $c->closed,
            ];
        }
        return $out;
    }

    public function syncAll(string $userId): array
    {
        return $this->sync($userId, 'all', null, null);
    }

    public function syncWorkspace(string $userId, string $workspaceTrelloId): array
    {
        $workspaceTrelloId = trim($workspaceTrelloId);
        if ($workspaceTrelloId === '') {
            throw new SynchronizationException('Workspace requerido.');
        }
        return $this->sync($userId, 'workspace', $workspaceTrelloId, null);
    }

    public function syncBoard(string $userId, string $boardTrelloId): array
    {
        $boardTrelloId = trim($boardTrelloId);
        if ($boardTrelloId === '') {
            throw new SynchronizationException('Board requerido.');
        }
        return $this->sync($userId, 'board', null, $boardTrelloId);
    }

    private function sync(string $userId, string $syncType, ?string $workspaceTrelloId, ?string $boardTrelloId): array
    {
        $startedAt = new DateTimeImmutable('now');
        $logId = $this->logs->start($userId, $syncType);

        $boardsProcessed = 0;
        $listsProcessed = 0;
        $cardsProcessed = 0;
        $errors = 0;

        try {
            $token = $this->tokenForUser($userId);
            $workspaces = $this->trello->getWorkspaces($token);

            foreach ($workspaces as $ws) {
                if ($workspaceTrelloId !== null && $ws->trelloId !== $workspaceTrelloId) {
                    continue;
                }

                $workspaceId = $this->workspaces->upsert($ws);
                $boards = $this->trello->getBoards($token, $ws->trelloId);
                $seenBoardIds = [];
                foreach ($boards as $b) {
                    $seenBoardIds[] = $b->trelloId;
                }

                foreach ($boards as $board) {
                    if ($boardTrelloId !== null && $board->trelloId !== $boardTrelloId) {
                        continue;
                    }

                    $this->syncBoardData($token, $workspaceId, $board, $listsProcessed, $cardsProcessed);
                    $boardsProcessed++;
                }

                if ($boardTrelloId === null) {
                    $this->boards->markClosedNotIn($workspaceId, $seenBoardIds);
                }
            }
        } catch (TrelloApiException $e) {
            $errors++;
            error_log('Trello sync error user=' . $userId . ' type=' . $syncType . ' msg=' . $e->getMessage());
            if (in_array($e->status(), [401, 403], true)) {
                throw new TrelloConnectionException('Trello: token inválido o sin permisos.');
            }
            throw new SynchronizationException($e->getMessage());
        } catch (Throwable $e) {
            $errors++;
            error_log('Trello sync error user=' . $userId . ' type=' . $syncType . ' msg=' . $e->getMessage());
            throw new SynchronizationException($e->getMessage());
        } finally {
            $finishedAt = new DateTimeImmutable('now');
            $this->logs->finish($logId, $boardsProcessed, $listsProcessed, $cardsProcessed, $errors, $finishedAt->format(DATE_ATOM));
            $this->connections->setLastSyncAt($userId, $finishedAt->format(DATE_ATOM));
        }

        $finishedAt = new DateTimeImmutable('now');
        $durationSeconds = (int)max(0, $finishedAt->getTimestamp() - $startedAt->getTimestamp());
        return [
            'boards' => $boardsProcessed,
            'lists' => $listsProcessed,
            'cards' => $cardsProcessed,
            'errors' => $errors,
            'started_at' => $startedAt->format(DATE_ATOM),
            'finished_at' => $finishedAt->format(DATE_ATOM),
            'duration_seconds' => $durationSeconds,
        ];
    }

    private function syncBoardData(string $token, int $workspaceId, BoardDTO $board, int &$listsProcessed, int &$cardsProcessed): void
    {
        $this->pdo->beginTransaction();
        try {
            $boardId = $this->boards->upsert($workspaceId, $board);

            $lists = $this->trello->getLists($token, $board->trelloId);
            $listIdByTrello = [];
            $seenListIds = [];
            foreach ($lists as $listDto) {
                $listId = $this->lists->upsert($boardId, $listDto);
                $listIdByTrello[$listDto->trelloId] = $listId;
                $seenListIds[] = $listDto->trelloId;
            }
            $listsProcessed += count($lists);
            $this->lists->markClosedNotIn($boardId, $seenListIds);

            $cards = $this->trello->getCards($token, $board->trelloId);
            $seenCardIds = [];
            foreach ($cards as $cardDto) {
                $listInternalId = $listIdByTrello[$cardDto->listTrelloId] ?? null;
                if (!is_int($listInternalId) || $listInternalId <= 0) {
                    continue;
                }
                $this->cards->upsert($boardId, $listInternalId, $cardDto);
                $cardsProcessed++;
                $seenCardIds[] = $cardDto->trelloId;
            }
            $this->cards->markClosedNotIn($boardId, $seenCardIds);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function tokenForUser(string $userId): string
    {
        $conn = $this->connections->getByUserId($userId);
        if ($conn === null || $conn->status !== 'connected') {
            throw new TrelloConnectionException('Cuenta Trello no conectada.');
        }
        $token = $this->safeDecrypt($conn->tokenEncrypted);
        if ($token === '') {
            throw new TrelloConnectionException('Cuenta Trello no conectada.');
        }
        return $token;
    }

    private function safeDecrypt(string $tokenEncrypted): string
    {
        try {
            return $this->crypto->decrypt($tokenEncrypted);
        } catch (Throwable) {
            return '';
        }
    }
}
