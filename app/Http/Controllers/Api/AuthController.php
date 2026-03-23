<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\User;
use App\Models\UserBrandProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $this->normalizePhone($request->phone),
            'password' => Hash::make($request->password),
        ]);

        $this->joinAllBrands($user);
        $this->sendOtpEmail($user);

        $token = $user->createToken('android')->plainTextToken;

        return response()->json([
            'message'        => 'Registrasi berhasil. Cek email untuk verifikasi OTP.',
            'user'           => $user,
            'token'          => $token,
            'email_verified' => false,
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $key  = 'otp:' . $user->id;
        $otp  = Cache::get($key);

        if (!$otp || $otp !== $request->otp) {
            return response()->json(['message' => 'OTP salah atau sudah kadaluarsa.'], 422);
        }

        $user->update(['email_verified_at' => now()]);
        Cache::forget($key);

        return response()->json(['message' => 'Email berhasil diverifikasi.']);
    }

    public function resendOtp(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email sudah terverifikasi.'], 422);
        }

        $this->sendOtpEmail($user);

        return response()->json(['message' => 'OTP baru telah dikirim ke email.']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->login)
            ->orWhere('phone', $this->normalizePhone($request->login))
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/nomor HP atau password salah.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('android')->plainTextToken;

        return response()->json([
            'message'        => 'Login berhasil.',
            'user'           => $user,
            'token'          => $token,
            'email_verified' => !is_null($user->email_verified_at),
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'google_id' => 'required|string',
            'email'     => 'required|email',
            'name'      => 'required|string',
            'avatar'    => 'nullable|string',
        ]);

        $user = User::where('google_id', $request->google_id)
            ->orWhere('email', $request->email)
            ->first();

        if ($user) {
            $user->update([
                'google_id'         => $request->google_id,
                'avatar'            => $request->avatar ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'google_id'         => $request->google_id,
                'avatar'            => $request->avatar,
                'password'          => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);
            $this->joinAllBrands($user);
        }

        $user->tokens()->delete();
        $token = $user->createToken('android')->plainTextToken;

        return response()->json([
            'message'        => 'Login Google berhasil.',
            'user'           => $user,
            'token'          => $token,
            'email_verified' => true,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user'           => $request->user()->load('brandProfiles.brand'),
            'email_verified' => !is_null($request->user()->email_verified_at),
        ]);
    }

    private function joinAllBrands(User $user): void
    {
        $brands = Brand::where('is_active', true)->get();
        foreach ($brands as $brand) {
            $prefix = strtoupper(substr($brand->slug, 0, 3));
            UserBrandProfile::firstOrCreate(
                ['user_id' => $user->id, 'brand_id' => $brand->id],
                [
                    'member_code'  => $prefix . '-' . strtoupper(Str::random(8)),
                    'total_points' => 0,
                    'tier'         => 'bronze',
                ]
            );
        }
    }

    private function sendOtpEmail(User $user): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp:' . $user->id, $otp, now()->addMinutes(10));

        Mail::send([], [], function ($message) use ($user, $otp) {
            $message->to($user->email, $user->name)
                ->subject('Kode OTP Verifikasi - Glamon Loyalty')
                ->html("
                    <div style='font-family:sans-serif;max-width:400px;margin:0 auto;padding:24px'>
                        <h2 style='color:#1a1a2e'>Verifikasi Email Anda</h2>
                        <p style='color:#666'>Gunakan kode OTP berikut untuk verifikasi akun Anda:</p>
                        <div style='background:#f5f5f5;border-radius:8px;padding:20px;text-align:center;margin:20px 0'>
                            <span style='font-size:36px;font-weight:700;letter-spacing:8px;color:#4f46e5'>{$otp}</span>
                        </div>
                        <p style='color:#999;font-size:13px'>Kode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapapun.</p>
                    </div>
                ");
        });
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return '+' . $phone;
    }
}
