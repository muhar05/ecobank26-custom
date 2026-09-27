<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Validated request data (current_password sudah divalidasi oleh ProfileUpdateRequest)
        $validated = $request->validated();

        // 2. Normalisasi nomor telepon
        $normalizedPhone = User::normalizePhone($validated['phone']);

        // 3. Pastikan nomor baru tidak digunakan user lain (defense in depth)
        if (User::where('phone', $normalizedPhone)->where('id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor telepon sudah digunakan oleh akun lain.',
            ]);
        }

        $phoneChanged = ($user->phone !== $normalizedPhone);

        // 5. Update user fields
        $user->name = $validated['name'];
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        $user->phone = $normalizedPhone;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // 6. Save (dengan proteksi terhadap race condition database unique constraint)
        try {
            $user->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor telepon sudah digunakan oleh akun lain.',
            ]);
        }

        // 7 & 8. Invalidate session dan redirect ke login jika nomor telepon berubah
        if ($phoneChanged) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Nomor telepon berhasil diperbarui. Silakan login kembali menggunakan nomor baru.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
