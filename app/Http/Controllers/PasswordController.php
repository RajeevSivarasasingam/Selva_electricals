<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function request(): View
    {
        return view('account-form', ['mode' => 'forgot']);
    }

    public function email(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If an account exists for this email, a password reset link has been requested. Check your inbox.');
    }

    public function reset(Request $request, string $token): View
    {
        return view('account-form', ['mode' => 'reset', 'token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:12', 'max:128', 'confirmed'],
        ]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'This reset link is invalid or expired. Please request another link.']);
        }

        return redirect()->route('login')->with('status', 'Password reset. Please sign in with your new password.');
    }
}
