<?php

use Core\Commands\ClearInternalCacheCommand;
use Core\Commands\MakeModuleCommand;
use Core\Commands\MakeModuleMigrationCommand;
use Core\Commands\MakeModuleModelCommand;
use Core\Middleware\ForceJsonResponse;
use Core\Middleware\RequestTimer;
use Core\Providers\ModuleManagerServiceProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: base_path('/core/routes/api.php'),
        commands: base_path('/core/routes/console.php'),
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            RequestTimer::class,
            ForceJsonResponse::class,
        ]);
        $middleware->remove([
            StartSession::class,
            VerifyCsrfToken::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })
    ->withCommands([
        MakeModuleCommand::class,
        MakeModuleModelCommand::class,
        MakeModuleMigrationCommand::class,
        ClearInternalCacheCommand::class,
    ])
    ->withProviders([
        ModuleManagerServiceProvider::class,
    ])
    ->create();

