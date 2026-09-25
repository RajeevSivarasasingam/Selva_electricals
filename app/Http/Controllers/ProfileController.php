<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('account-form', ['mode' => 'profile']);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+() .-]+$/'],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['nullable', 'string', 'min:12', 'max:128', 'confirmed'],
        ]);
        $user = $request->user();
        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->setRememberToken(Str::random(60));
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
            }
        }
        $user->save();

        return back()->with('status', 'Profile updated.');
    }
}
