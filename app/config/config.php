<?php
declare(strict_types=1);

$supabaseUrl = getenv('SUPABASE_URL') ?: '';
$supabaseAnonKey = getenv('SUPABASE_ANON_KEY') ?: '';

return [
    'supabase' => [
        'url' => rtrim($supabaseUrl, '/'),
        'anon_key' => $supabaseAnonKey,
    ],
    'app' => [
        'name' => getenv('APP_NAME') ?: 'Monitoreo de Proyectos',
        'base_url' => getenv('APP_BASE_URL') ?: '',
    ],
];

