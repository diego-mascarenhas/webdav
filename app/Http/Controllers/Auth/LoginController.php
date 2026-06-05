<?php

namespace App\Http\Controllers\Auth;

use App\Auth\DavUserResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('contacts.index');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $user = DavUserResolver::validate($validated['login'], $validated['password']);

        if ($user === null) {
            return back()->withErrors([
                'login' => 'Usuario o contraseña incorrectos.',
            ])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('contacts.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
