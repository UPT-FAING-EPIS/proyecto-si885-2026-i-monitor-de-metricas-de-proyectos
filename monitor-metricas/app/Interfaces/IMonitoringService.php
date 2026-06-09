<?php
declare(strict_types=1);

namespace App\Interfaces;

interface IMonitoringService
{
    /** @return array<string,mixed> */
    public function getDashboardData(string $userId): array;

    /** @return array<string,mixed> */
    public function getProjectsData(string $userId): array;

    /** @return array<string,mixed> */
    public function getProjectDetailData(string $userId, string $projectId): array;

    /** @return array<string,mixed> */
    public function getAnalyticsData(string $userId): array;

    /** @return array<string,mixed> */
    public function getAlertsData(string $userId): array;
}
