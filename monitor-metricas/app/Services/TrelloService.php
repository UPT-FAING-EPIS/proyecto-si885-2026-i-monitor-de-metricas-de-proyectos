<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpClient;
use App\DTOs\BoardDTO;
use App\DTOs\CardDTO;
use App\DTOs\ListDTO;
use App\DTOs\WorkspaceDTO;
use App\Exceptions\TrelloApiException;
use App\Interfaces\ITrelloService;

final class TrelloService implements ITrelloService
{
    private const BASE_URL = 'https://api.trello.com/1';

    public function __construct(private readonly HttpClient $http)
    {
    }

    public function getMember(string $token): array
    {
        $data = $this->get('/members/me', $token, [
            'fields' => 'id,email,fullName,username',
        ]);

        return [
            'id' => (string)($data['id'] ?? ''),
            'email' => isset($data['email']) ? (string)$data['email'] : null,
            'fullName' => isset($data['fullName']) ? (string)$data['fullName'] : null,
            'username' => isset($data['username']) ? (string)$data['username'] : null,
        ];
    }

    public function getWorkspaces(string $token): array
    {
        $items = $this->get('/members/me/organizations', $token, [
            'fields' => 'id,displayName,name,desc',
        ]);

        $out = [];
        if (is_array($items)) {
            foreach ($items as $w) {
                if (!is_array($w)) {
                    continue;
                }
                $id = (string)($w['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $name = (string)($w['displayName'] ?? $w['name'] ?? '');
                $desc = isset($w['desc']) && (string)$w['desc'] !== '' ? (string)$w['desc'] : null;
                $out[] = new WorkspaceDTO($id, $name !== '' ? $name : $id, $desc);
            }
        }
        return $out;
    }

    public function getBoards(string $token, string $workspaceTrelloId): array
    {
        $items = $this->get('/organizations/' . rawurlencode($workspaceTrelloId) . '/boards', $token, [
            'filter' => 'all',
            'fields' => 'id,name,desc,url,closed,idOrganization',
        ]);

        $out = [];
        if (is_array($items)) {
            foreach ($items as $b) {
                if (!is_array($b)) {
                    continue;
                }
                $id = (string)($b['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $name = (string)($b['name'] ?? $id);
                $desc = isset($b['desc']) && (string)$b['desc'] !== '' ? (string)$b['desc'] : null;
                $url = isset($b['url']) && (string)$b['url'] !== '' ? (string)$b['url'] : null;
                $closed = (bool)($b['closed'] ?? false);
                $out[] = new BoardDTO($id, $workspaceTrelloId, $name, $desc, $url, $closed);
            }
        }
        return $out;
    }

    public function getLists(string $token, string $boardTrelloId): array
    {
        $items = $this->get('/boards/' . rawurlencode($boardTrelloId) . '/lists', $token, [
            'filter' => 'all',
            'fields' => 'id,name,closed',
        ]);

        $out = [];
        if (is_array($items)) {
            foreach ($items as $l) {
                if (!is_array($l)) {
                    continue;
                }
                $id = (string)($l['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $name = (string)($l['name'] ?? $id);
                $closed = (bool)($l['closed'] ?? false);
                $out[] = new ListDTO($id, $boardTrelloId, $name, $closed);
            }
        }
        return $out;
    }

    public function getCards(string $token, string $boardTrelloId): array
    {
        $items = $this->get('/boards/' . rawurlencode($boardTrelloId) . '/cards/all', $token, [
            'fields' => 'id,name,desc,closed,due,idList',
        ]);

        $out = [];
        if (is_array($items)) {
            foreach ($items as $c) {
                if (!is_array($c)) {
                    continue;
                }
                $id = (string)($c['id'] ?? '');
                $listId = (string)($c['idList'] ?? '');
                if ($id === '' || $listId === '') {
                    continue;
                }
                $name = (string)($c['name'] ?? $id);
                $desc = isset($c['desc']) && (string)$c['desc'] !== '' ? (string)$c['desc'] : null;
                $due = isset($c['due']) && (string)$c['due'] !== '' ? (string)$c['due'] : null;
                $closed = (bool)($c['closed'] ?? false);
                $out[] = new CardDTO($id, $boardTrelloId, $listId, $name, $desc, $due, $closed);
            }
        }
        return $out;
    }

    /** @param array<string,string> $query */
    private function get(string $path, string $token, array $query = []): mixed
    {
        $key = $this->apiKey();
        $query = array_merge($query, [
            'key' => $key,
            'token' => $token,
        ]);

        $resp = $this->http->request('GET', self::BASE_URL . $path, [], $query);
        if (($resp['ok'] ?? false) !== true) {
            $status = (int)($resp['status'] ?? 0);
            $msg = $this->messageFrom($resp);
            throw new TrelloApiException($msg, $status);
        }
        return $resp['data'] ?? null;
    }

    private function apiKey(): string
    {
        $key = (string)($_ENV['TRELLO_API_KEY'] ?? $_SERVER['TRELLO_API_KEY'] ?? getenv('TRELLO_API_KEY') ?: '');
        if ($key === '') {
            throw new TrelloApiException('Trello no está configurado (TRELLO_API_KEY).', 0);
        }
        return $key;
    }

    /** @param array<string,mixed> $resp */
    private function messageFrom(array $resp): string
    {
        $status = (int)($resp['status'] ?? 0);
        $data = $resp['data'] ?? null;
        $body = is_string($resp['body'] ?? null) ? (string)$resp['body'] : '';
        $msg = '';

        if (is_array($data)) {
            $msg = (string)($data['message'] ?? $data['error'] ?? $data['msg'] ?? '');
        }
        if ($msg === '' && $body !== '') {
            $msg = $body;
        }
        if ($msg === '') {
            $msg = 'Error Trello API.';
        }
        return $status > 0 ? ($msg . ' (HTTP ' . $status . ')') : $msg;
    }
}

