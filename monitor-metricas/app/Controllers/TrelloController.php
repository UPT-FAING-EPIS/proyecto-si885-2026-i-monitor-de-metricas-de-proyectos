<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\SynchronizationException;
use App\Exceptions\TrelloConnectionException;
use App\Interfaces\IProjectMetricsService;
use App\Interfaces\ITrelloSyncService;
use Throwable;

final class TrelloController extends Controller
{
    public function __construct(
        private readonly ITrelloSyncService $trelloSync,
        private readonly IProjectMetricsService $metrics,
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $this->requireAuth($response);
        $userId = (string)($_SESSION['user']['id'] ?? '');
        $status = $userId !== '' ? $this->trelloSync->status($userId) : ['connected' => false];
        $metrics = $userId !== '' ? $this->metrics->getOverview($userId) : [
            'summary' => [
                'workspaces' => 0,
                'boards' => 0,
                'lists' => 0,
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'pending_tasks' => 0,
                'overdue_tasks' => 0,
                'progress_percentage' => 0.0,
            ],
            'boards' => [],
            'latest_sync' => null,
            'recent_logs' => [],
        ];
        $this->render('pages/trello', ['trelloStatus' => $status, 'trelloMetrics' => $metrics, 'csrf' => $_SESSION['csrf'] ?? '']);
    }

    public function status(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        $response->json(['ok' => true, 'data' => $this->trelloSync->status($userId)]);
    }

    public function connect(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        if (($request->csrf() ?: '') !== ($_SESSION['csrf'] ?? null)) {
            $response->json(['ok' => false, 'error' => 'CSRF inválido.'], 419);
            return;
        }

        $token = (string)$request->input('token', '');
        try {
            $data = $this->trelloSync->connect($userId, $token);
            $response->json(['ok' => true, 'data' => $data]);
        } catch (TrelloConnectionException $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => 'Error al conectar Trello.'], 500);
        }
    }

    public function disconnect(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        if (($request->csrf() ?: '') !== ($_SESSION['csrf'] ?? null)) {
            $response->json(['ok' => false, 'error' => 'CSRF inválido.'], 419);
            return;
        }

        $this->trelloSync->disconnect($userId);
        $response->json(['ok' => true]);
    }

    public function member(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        try {
            $response->json(['ok' => true, 'data' => $this->trelloSync->getMember($userId)]);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function workspaces(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        try {
            $response->json(['ok' => true, 'data' => $this->trelloSync->getWorkspaces($userId)]);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function boards(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        $workspaceId = (string)$request->input('workspace_id', '');
        try {
            $response->json(['ok' => true, 'data' => $this->trelloSync->getBoards($userId, $workspaceId)]);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function lists(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        $boardId = (string)$request->input('board_id', '');
        try {
            $response->json(['ok' => true, 'data' => $this->trelloSync->getLists($userId, $boardId)]);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function cards(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        $boardId = (string)$request->input('board_id', '');
        try {
            $response->json(['ok' => true, 'data' => $this->trelloSync->getCards($userId, $boardId)]);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function metrics(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        try {
            $response->json(['ok' => true, 'data' => $this->metrics->getOverview($userId)]);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => 'No se pudieron calcular las métricas del proyecto.'], 500);
        }
    }

    public function sync(Request $request, Response $response): void
    {
        $userId = (string)($_SESSION['user']['id'] ?? '');
        if ($userId === '') {
            $response->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }
        if (($request->csrf() ?: '') !== ($_SESSION['csrf'] ?? null)) {
            $response->json(['ok' => false, 'error' => 'CSRF inválido.'], 419);
            return;
        }

        $type = (string)$request->input('type', 'all');
        $workspaceId = (string)$request->input('workspace_id', '');
        $boardId = (string)$request->input('board_id', '');

        try {
            if ($type === 'workspace') {
                $data = $this->trelloSync->syncWorkspace($userId, $workspaceId);
            } elseif ($type === 'board') {
                $data = $this->trelloSync->syncBoard($userId, $boardId);
            } else {
                $data = $this->trelloSync->syncAll($userId);
            }
            $response->json(['ok' => true, 'data' => $data]);
        } catch (TrelloConnectionException $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 401);
        } catch (SynchronizationException $e) {
            $response->json(['ok' => false, 'error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $response->json(['ok' => false, 'error' => 'Error de sincronización.'], 500);
        }
    }
}

