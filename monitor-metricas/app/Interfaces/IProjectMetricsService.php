<?php
declare(strict_types=1);

namespace App\Interfaces;

interface IProjectMetricsService
{
    /**
     * @return array{
     *   summary: array{
     *     workspaces:int,
     *     boards:int,
     *     lists:int,
     *     total_tasks:int,
     *     completed_tasks:int,
     *     pending_tasks:int,
     *     overdue_tasks:int,
     *     progress_percentage:float
     *   },
     *   boards: list<array{
     *     board_id:int,
     *     trello_board_id:string,
     *     name:string,
     *     workspace_name:?string,
     *     total_tasks:int,
     *     completed_tasks:int,
     *     pending_tasks:int,
     *     overdue_tasks:int,
     *     progress_percentage:float
     *   }>,
     *   latest_sync: ?array{
     *     sync_type:string,
     *     boards_processed:int,
     *     lists_processed:int,
     *     cards_processed:int,
     *     errors_count:int,
     *     started_at:string,
     *     finished_at:?string
     *   },
     *   recent_logs: list<array{
     *     sync_type:string,
     *     boards_processed:int,
     *     lists_processed:int,
     *     cards_processed:int,
     *     errors_count:int,
     *     started_at:string,
     *     finished_at:?string
     *   }>
     * }
     */
    public function getOverview(string $userId): array;
}
