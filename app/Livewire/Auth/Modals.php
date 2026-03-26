<?php

namespace App\Livewire\Auth;

use App\Mail\AccountPendingApprovalMail;
use App\Mail\WelcomeUserMail;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Services\TenantMailService;
use App\Support\LegalAcceptance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\On;
use Livewire\Component;

class Modals extends Component
{
    public bool $isOpen = false;
    public string $mode = 'login'; // login, register, forgot-password, pending-approval

    // Login Properties
    public $email = '';
    public $password = '';
    public $remember = false;

    // Register Properties
    public $name = '';
    public $registerEmail = '';
    public $registerPassword = '';
    public $registerPasswordConfirmation = '';
    public $acceptLegalTerms = false;

    // Forgot Password Properties
    public $forgotEmail = '';
    public $resetLinkSent = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    #[On('open-auth-modal')]
    public function openModal($mode = 'login')
    {
        $this->mode = $mode;
        $this->isOpen = true;
        $this->resetValidation();
        $this->reset(['email', 'password', 'name', 'registerEmail', 'registerPassword', 'registerPasswordConfirmation', 'acceptLegalTerms', 'forgotEmail', 'resetLinkSent']);
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function switchMode($mode)
    {
        $this->mode = $mode;
        $this->resetValidation();
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $this->email)->first();

        if ($user && ! $user->is_active) {
            $this->addError('email', __('Tu cuenta está pendiente de aprobación. Recibirás un correo cuando sea activada.'));

            return;
        }

        $previousSessionId = session()->getId();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            Auth::user()->updateLastLogin(request()->ip());

            // Manual Cart Merge
            $this->mergeCart($previousSessionId, Auth::user());

            $this->close();
            $this->js("window.location.reload()");
        } else {
            $this->addError('email', __('These credentials do not match our records.'));
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'registerEmail' => 'required|string|email|max:255|unique:users,email',
            'registerPassword' => 'required|string|min:8|same:registerPasswordConfirmation',
            'acceptLegalTerms' => 'accepted',
        ]);

        $previousSessionId = session()->getId();
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        $requiresApproval = GeneralSetting::requiresAccountApproval();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->registerEmail,
            'password' => Hash::make($this->registerPassword),
            'legal_acceptance' => LegalAcceptance::snapshot(request()),
            'tenant_id' => $tenant?->id,
            'is_active' => ! $requiresApproval,
        ]);

        $mailService = app(TenantMailService::class);

        if ($requiresApproval) {
            $mailService->send(new AccountPendingApprovalMail($user));

            // Notify tenant admins
            $admins = User::where('tenant_id', $tenant?->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'super_admin']))
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewUserRegistered($user));
            }

            $this->mode = 'pending-approval';

            return;
        }

        Auth::login($user);
        session()->regenerate();

        $user->updateLastLogin(request()->ip());

        $mailService->send(new WelcomeUserMail($user));

        // Manual Cart Merge
        $this->mergeCart($previousSessionId, $user);

        $this->close();
        $this->js("window.location.reload()");
    }

    public function sendResetLink(): void
    {
        $this->validate([
            'forgotEmail' => 'required|email',
        ]);

        $status = Password::sendResetLink(['email' => $this->forgotEmail]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->resetLinkSent = true;
        } else {
            $this->addError('forgotEmail', __($status));
        }
    }

    protected function mergeCart($sessionId, $user)
    {
        $guestCart = \App\Models\Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();

        if ($guestCart) {
            $guestCart->assignToUser($user);
        }
    }

    public function render()
    {
        return view('livewire.auth.modals');
    }
}
