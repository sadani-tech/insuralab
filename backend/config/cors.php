<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The public API is browser-facing, so the Next.js frontend origin(s) must be
| allowed explicitly. Origins are environment-driven — never a wildcard in a
| deployed environment. Set `CORS_ALLOWED_ORIGINS` to a comma-separated list,
| or rely on `FRONTEND_URL` for the single-origin local setup.
|
*/

$origins = collect(explode(',', (string) env('CORS_ALLOWED_ORIGINS', (string) env('FRONTEND_URL', 'http://localhost:3000'))))
    ->map(fn (string $origin) => trim($origin))
    ->filter()
    ->unique()
    ->values()
    ->all();

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['GET', 'HEAD', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With', 'X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 60 * 60,

    // Needed for the stateful Sanctum admin panel login flow. The public read
    // API does not depend on credentials.
    'supports_credentials' => true,

];
