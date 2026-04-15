<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Shares global Inertia props (app name, auth, UI state) on every response.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version for cache busting.
     *
     * @param  Request  $request  Current HTTP request
     * @return string|null Version token or null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default with the Inertia root.
     *
     * @param  Request  $request  Current HTTP request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $authenticated = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'developer' => $authenticated,
                // Wayfinder / Inertia defaults expect `auth.user`; same model instance as `developer`.
                'user' => $authenticated,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
