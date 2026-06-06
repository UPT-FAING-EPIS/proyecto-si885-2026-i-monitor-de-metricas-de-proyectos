<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\DTOs\BoardDTO;
use App\DTOs\CardDTO;
use App\DTOs\ListDTO;
use App\DTOs\WorkspaceDTO;

interface ITrelloService
{
    /** @return array{id:string,email?:string,fullName?:string,username?:string} */
    public function getMember(string $token): array;

    /** @return list<WorkspaceDTO> */
    public function getWorkspaces(string $token): array;

    /** @return list<BoardDTO> */
    public function getBoards(string $token, string $workspaceTrelloId): array;

    /** @return list<ListDTO> */
    public function getLists(string $token, string $boardTrelloId): array;

    /** @return list<CardDTO> */
    public function getCards(string $token, string $boardTrelloId): array;
}

