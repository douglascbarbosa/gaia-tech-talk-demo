<?php

namespace App\Http\Controllers\Settings;

use App\Application\Developer\DeveloperApplicationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Settings UI for viewing, updating, and deleting the authenticated developer profile.
 */
class ProfileController extends Controller
{
    /**
     * @param  DeveloperApplicationService  $developerApplicationService  Profile persistence use cases
     */
    public function __construct(
        private readonly DeveloperApplicationService $developerApplicationService,
    ) {}

    /**
     * Show the profile settings page (name, email, portfolio fields).
     *
     * @param  Request  $request  Current request (authenticated)
     * @return Response Inertia response for the profile settings page
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Validate and persist profile updates via the application service.
     *
     * @param  ProfileUpdateRequest  $request  Validated profile payload
     * @return RedirectResponse Redirect back to the profile edit route
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->developerApplicationService->update(
            $request->user(),
            $request->validated(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Log out, delete the developer row, and invalidate the session.
     *
     * @param  ProfileDeleteRequest  $request  Password confirmation for destructive action
     * @return RedirectResponse Redirect to the application home page
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $developer = $request->user();

        Auth::logout();

        $this->developerApplicationService->delete($developer);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
