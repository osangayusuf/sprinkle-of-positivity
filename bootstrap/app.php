<?php

use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RecordPageVisit;
use App\Models\PageVisit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            RecordPageVisit::class,
        ]);

        $middleware->alias([
            'onboarded' => EnsureOnboardingIsComplete::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // No persistent worker on cPanel — a single cron entry running
        // `schedule:run` every minute drains the queue in short bursts
        // instead. `--stop-when-empty` exits as soon as there's nothing
        // left, and `--max-time=55` guards against overlapping the next run.
        $schedule->command('queue:work --stop-when-empty --max-time=55')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('certificates:issue')->dailyAt('01:00');

        // Migrations and cache rebuilds after an FTP deploy (see FinishDeploy).
        $schedule->command('deploy:finish')->everyMinute()->withoutOverlapping();

        $schedule->command('model:prune', ['--model' => [PageVisit::class]])->dailyAt('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
