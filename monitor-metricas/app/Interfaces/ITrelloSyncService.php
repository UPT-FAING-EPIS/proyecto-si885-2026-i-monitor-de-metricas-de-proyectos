<?php
declare(strict_types=1);

namespace App\Interfaces;

interface ITrelloSyncService
{
    /** @return array{connected:bool,trello_member_id?:string,last_sync_at?:string,status?:string} */
    public function status(string $userId): array;

    /** @return array{connected:bool,trello_member_id:string} */
    public function connect(string $userId, string $token): array;

    public function disconnect(string $userId): void;

    /** @return array{id:string,email?:string,fullName?:string,username?:string} */
    public function getMember(string $userId): array;

    /** @return list<array{trello_id:string,name:string,description:?string}> */
    public function getWorkspaces(string $userId): array;

    /** @return list<array{trello_id:string,workspace_trello_id:string,name:string,description:?string,url:?string,closed:bool}> */
    public function getBoards(string $userId, string $workspaceTrelloId): array;

    /** @return list<array{trello_id:string,board_trello_id:string,name:string,closed:bool}> */
    public function getLists(string $userId, string $boardTrelloId): array;

    /** @return list<array{trello_id:string,board_trello_id:string,list_trello_id:string,name:string,description:?string,due_date:?string,closed:bool}> */
    public function getCards(string $userId, string $boardTrelloId): array;

    /** @return array{boards:int,lists:int,cards:int,errors:int,started_at:string,finished_at:string,duration_seconds:int} */
    public function syncAll(string $userId): array;

    /** @return array{boards:int,lists:int,cards:int,errors:int,started_at:string,finished_at:string,duration_seconds:int} */
    public function syncWorkspace(string $userId, string $workspaceTrelloId): array;

    /** @return array{boards:int,lists:int,cards:int,errors:int,started_at:string,finished_at:string,duration_seconds:int} */
    public function syncBoard(string $userId, string $boardTrelloId): array;
}
