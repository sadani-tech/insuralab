<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // This guard runs before RefreshDatabase can touch the schema.
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.'.$connection.'.database');
        if (! $app->environment('testing') || $database !== 'insuralab_testing') {
            throw new \RuntimeException('Refusing tests outside the isolated insuralab_testing database.');
        }

        return $app;
    }
}
