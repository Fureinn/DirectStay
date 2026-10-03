<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerSettingsController extends Controller
{
    /**
     * Display the signed-in guest's account settings.
     */
    public function edit(Request $request): View
    {
        abort_unless($request->user()->isGuest(), 403);

        return view('customer.settings.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the signed-in guest's contact details and optional password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isGuest(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => ['required', 'string', 'max:30'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        if (! empty($validated['password'])) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->update($attributes);

        return back()->with('success', 'Your account settings have been updated. Future booking forms will use these details.');
    }
}
