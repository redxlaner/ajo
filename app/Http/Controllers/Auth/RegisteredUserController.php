<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => preg_replace('/\s+/', '', $request->phone),
            'password' => Hash::make($request->password),
        ]);

        // Invite code + who invited them
        $user->invite_code = strtoupper(substr(md5(uniqid((string) $user->id, true)), 0, 8));
        if (session('invited_by')) {
            $user->invited_by = session('invited_by');
            session()->forget('invited_by');
        }
        $user->save();

                event(new Registered($user));
        Auth::login($user);

        $redirect = $request->input('redirect');
        if ($redirect && $this->isSafeRedirect($redirect)) {
            return redirect()->to($redirect);
        }

        if (session('team_invite_slug')) {
            $slug = session()->pull('team_invite_slug');
            $projectId = session()->pull('project_invite_id');

            if ($projectId) {
                return redirect()->route('projects.invite-link', [$slug, $projectId]);
            }

            return redirect()->route('teams.join-link', $slug);
        }

        return redirect(route('dashboard', absolute: false));
    }

    protected function isSafeRedirect(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//');
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        return $urlHost && $appHost && strcasecmp($urlHost, $appHost) === 0;
    }
}
