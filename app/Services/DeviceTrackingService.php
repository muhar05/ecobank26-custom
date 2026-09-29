<?php

namespace App\Services;

use App\Models\AdminDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class DeviceTrackingService
{
    public const COOKIE_NAME = 'admin_device_token';

    /**
     * Parse browser and operating system platform from user agent string.
     */
    public function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return ['browser' => 'Unknown', 'platform' => 'Unknown'];
        }

        $ua = $userAgent;

        // Platform Detection
        $platform = 'Unknown';
        if (preg_match('/windows|win32/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $platform = 'iOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        }

        // Browser Detection
        $browser = 'Unknown';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/opr|opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) {
            $browser = 'Safari';
        }

        return [
            'browser'  => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Get active device from current request and authenticated user.
     */
    public function getDeviceFromRequest(Request $request, User $user): ?AdminDevice
    {
        $token = $request->cookie(self::COOKIE_NAME);
        if (!$token) {
            return null;
        }

        $device = AdminDevice::where('device_token', $token)
            ->where('user_id', $user->id)
            ->where('is_revoked', false)
            ->first();

        if ($device) {
            $device->update([
                'last_seen_at' => now(),
                'last_ip'      => $request->ip(),
            ]);
            return $device;
        }

        return null;
    }

    /**
     * Get or initialize a device registration.
     */
    public function getOrCreateDevice(Request $request, User $user): array
    {
        $existing = $this->getDeviceFromRequest($request, $user);
        if ($existing) {
            return [
                'device' => $existing,
                'is_new' => false,
                'cookie' => null,
            ];
        }

        $token = Str::random(64);
        $parsed = $this->parseUserAgent($request->userAgent());

        $device = AdminDevice::create([
            'user_id'       => $user->id,
            'device_token'  => $token,
            'device_name'   => null,
            'browser'       => $parsed['browser'],
            'platform'      => $parsed['platform'],
            'user_agent'    => $request->userAgent() ? substr($request->userAgent(), 0, 500) : null,
            'last_ip'       => $request->ip(),
            'is_revoked'    => false,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
        ]);

        // 5 years expiration cookie, httpOnly, sameSite lax
        $cookie = cookie(
            self::COOKIE_NAME,
            $token,
            60 * 24 * 365 * 5,
            '/',
            null,
            null,
            true,
            false,
            'lax'
        );

        return [
            'device' => $device,
            'is_new' => true,
            'cookie' => $cookie,
        ];
    }

    /**
     * Set a friendly name for an admin device.
     */
    public function nameDevice(AdminDevice $device, string $name): AdminDevice
    {
        $device->update([
            'device_name' => trim($name),
        ]);

        return $device->fresh();
    }

    /**
     * Revoke access for a specific device.
     */
    public function revokeDevice(AdminDevice $device): bool
    {
        return $device->update([
            'is_revoked' => true,
        ]);
    }
}
