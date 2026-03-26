<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class LogoUploadController extends Controller
{
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:5120',
        ]);

        $tenantDir = $this->getTenantDirectory();
        $path = $request->file('logo')->store($tenantDir . '/settings', 'public');

        $settings = GeneralSetting::first();

        if (! $settings) {
            return redirect()->back()->with('error', 'Settings not found.');
        }

        $settings->update(['site_logo' => $path]);

        return redirect()->route('filament.admin.pages.manage-general-settings')
            ->with('success', 'Logo uploaded successfully!');
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|max:1024',
        ]);

        $tenantDir = $this->getTenantDirectory();
        $path = $request->file('favicon')->store($tenantDir . '/settings', 'public');

        $settings = GeneralSetting::first();

        if (! $settings) {
            return redirect()->back()->with('error', 'Settings not found.');
        }

        $settings->update(['site_favicon' => $path]);

        return redirect()->route('filament.admin.pages.manage-general-settings')
            ->with('success', 'Favicon uploaded successfully!');
    }

    private function getTenantDirectory(): string
    {
        if (! app()->bound('current_tenant') || ! app('current_tenant')) {
            abort(403, 'Tenant context required.');
        }

        return 'tenant-' . app('current_tenant')->id;
    }
}
