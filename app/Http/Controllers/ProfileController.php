<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('name', 'email'));
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'updatePassword');
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'], 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Show profile view
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'userDeletion');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'The password is incorrect.'], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Your account has been deleted successfully.');
    }
}