<?php
declare(strict_types=1);

namespace App\Services;

use App\Interfaces\IProjectMetricsService;
use App\Repositories\ProjectMetricsRepository;

final class ProjectMetricsService implements IProjectMetricsService
{
    public function __construct(private readonly ProjectMetricsRepository $metrics)
    {
    }

    public function getOverview(string $userId): array
    {
        return [
            'summary' => $this->metrics->getSummary($userId),
            'boards' => $this->metrics->getBoardBreakdown($userId),
            'latest_sync' => $this->metrics->getLatestSyncForUser($userId),
            'recent_logs' => $this->metrics->getRecentLogsForUser($userId),
        ];
    }
}
