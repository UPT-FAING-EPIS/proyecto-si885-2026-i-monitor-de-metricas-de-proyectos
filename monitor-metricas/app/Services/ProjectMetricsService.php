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
            'summary' => $this->metrics->getSummary(),
            'boards' => $this->metrics->getBoardBreakdown(),
            'latest_sync' => $this->metrics->getLatestSyncForUser($userId),
        ];
    }
}
