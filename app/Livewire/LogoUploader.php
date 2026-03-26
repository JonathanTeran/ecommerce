<?php

namespace App\Livewire;

use App\Models\GeneralSetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class LogoUploader extends Component
{
    use WithFileUploads;

    public $logo;

    public $favicon;

    public $currentLogo;

    public $currentFavicon;

    public function mount()
    {
        $settings = GeneralSetting::first();
        $this->currentLogo = $settings?->site_logo;
        $this->currentFavicon = $settings?->site_favicon;
    }

    public function uploadLogo()
    {
        $this->validate([
            'logo' => 'image|max:5120', // 5MB Max
        ]);

        $path = $this->logo->store('settings', 'public');

        $settings = GeneralSetting::first();
        $settings->update(['site_logo' => $path]);

        $this->currentLogo = $path;
        $this->logo = null;

        session()->flash('message', 'Logo uploaded successfully!');
    }

    public function uploadFavicon()
    {
        $this->validate([
            'favicon' => 'image|max:1024', // 1MB Max
        ]);

        $path = $this->favicon->store('settings', 'public');

        $settings = GeneralSetting::first();
        $settings->update(['site_favicon' => $path]);

        $this->currentFavicon = $path;
        $this->favicon = null;

        session()->flash('message', 'Favicon uploaded successfully!');
    }

    public function render()
    {
        return view('livewire.logo-uploader')
            ->layout('layouts.app');
    }
}
