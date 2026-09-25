<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Insight;
use App\Models\QuizResponse;
use App\Models\Role;
use App\Models\User;
use App\Observers\CommentObserver;
use App\Observers\InsightObserver;
use App\Observers\QuizResponseObserver;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // The framework default checks for a route named "dashboard" before
        // "home", which would send already-authenticated visitors to the
        // legacy scaffold page instead of the app's real home feed.
        RedirectIfAuthenticated::redirectUsing(fn () => route('home'));

        Gate::define('access-admin', fn (User $user) => $user->hasRole(Role::ADMIN));

        Insight::observe(InsightObserver::class);
        Comment::observe(CommentObserver::class);
        QuizResponse::observe(QuizResponseObserver::class);

        // Inertia props aren't REST responses — drop the "data" wrapper so
        // every API Resource serializes as a plain array/object for the
        // frontend.
        JsonResource::withoutWrapping();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
