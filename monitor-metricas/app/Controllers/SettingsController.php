<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class SettingsController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $this->requireAuth($response);
        $this->render('pages/settings');
    }
}

