<?php

namespace App\Http\Controllers;

use App\Models\AdminDevice;
use App\Services\DeviceTrackingService;
use Illuminate\Http\Request;

class AdminDeviceController extends Controller
{
    protected DeviceTrackingService $deviceService;

    public function __construct(DeviceTrackingService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Show form to name an un-named device.
     */
    public function nameDeviceForm(Request $request)
    {
        $device = $request->attributes->get('admin_device') 
            ?? $this->deviceService->getDeviceFromRequest($request, $request->user());

        if (!$device) {
            $res = $this->deviceService->getOrCreateDevice($request, $request->user());
            $device = $res['device'];
        }

        if ($device->isNamed()) {
            return redirect()->route('bank-sampah.devices.index');
        }

        return view('bank-sampah.devices.name', compact('device'));
    }

    /**
     * Save the friendly device name.
     */
    public function storeDeviceName(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:100',
        ]);

        $device = $request->attributes->get('admin_device') 
            ?? $this->deviceService->getDeviceFromRequest($request, $request->user());

        if (!$device) {
            $res = $this->deviceService->getOrCreateDevice($request, $request->user());
            $device = $res['device'];
        }

        $this->deviceService->nameDevice($device, $request->input('device_name'));

        return redirect()->intended(route('bank-sampah.waste-prices.index'))
            ->with('success', "Perangkat '{$device->device_name}' berhasil disimpan.");
    }

    /**
     * List all devices for the authenticated admin user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $currentDevice = $request->attributes->get('admin_device') 
            ?? $this->deviceService->getDeviceFromRequest($request, $user);

        $devices = $user->adminDevices()->latest('last_seen_at')->get();

        return view('bank-sampah.devices.index', compact('devices', 'currentDevice'));
    }

    /**
     * Revoke access for a specific device.
     */
    public function revoke(Request $request, AdminDevice $device)
    {
        if ($device->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak untuk mencabut akses perangkat ini.');
        }

        $this->deviceService->revokeDevice($device);

        return back()->with('success', "Akses untuk perangkat '{$device->device_name}' telah dicabut.");
    }
}
