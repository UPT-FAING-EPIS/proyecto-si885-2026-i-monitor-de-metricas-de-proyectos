<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Interfaces\IMonitoringService;

final class AlertsController extends Controller
{
    public function __construct(private readonly IMonitoringService $monitoring)
    {
    }

    public function index(Request $request, Response $response): void
    {
        $this->requireAuth($response);
        $userId = (string)($_SESSION['user']['id'] ?? '');
        $this->render('pages/alerts', ['payload' => $this->monitoring->getAlertsData($userId)]);
    }
}
