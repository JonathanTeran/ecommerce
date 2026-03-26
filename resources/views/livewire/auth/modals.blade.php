<div>
    <div x-data="{ show: @entangle('isOpen').live }" x-init="console.log('Auth Modal Initialized')"
        x-on:open-auth-modal.window="console.log('Event Received:', $event.detail); $wire.openModal($event.detail.mode)"
        x-show="show" x-on:keydown.escape.window="show = false" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"
            @click="show = false"></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">

                <!-- Close Button -->
                <button @click="show = false"
                    class="absolute right-4 top-4 text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center mb-6">
                            @if($tenantSettings?->site_logo)
                                <img src="{{ url('storage/' . $tenantSettings->site_logo) }}" alt="{{ $tenantSettings->site_name ?? config('app.name') }}"
                                    class="h-16 w-auto object-contain">
                            @else
                                <span class="font-heading font-bold text-2xl text-gray-900 dark:text-white">
                                    {{ $tenantSettings?->site_name ?? config('app.name') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-2xl font-bold font-heading leading-6 text-gray-900 dark:text-white"
                            id="modal-title">
                            @if($mode === 'login')
                                {{ __('Welcome Back') }}
                            @elseif($mode === 'forgot-password')
                                {{ __('Reset Password') }}
                            @elseif($mode === 'pending-approval')
                                {{ __('Registro Recibido') }}
                            @else
                                {{ __('Create Account') }}
                            @endif
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            @if($mode === 'login')
                                {{ __('Enter your credentials to access your account.') }}
                            @elseif($mode === 'forgot-password')
                                {{ __('Enter your email and we will send you a reset link.') }}
                            @elseif($mode === 'pending-approval')
                                {{ __('Tu solicitud está siendo revisada.') }}
                            @else
                                {{ __('Join our community to start shopping.') }}
                            @endif
                        </p>
                    </div>

                    <div class="mt-8">
                        @if ($mode === 'login')
                            <form wire:submit.prevent="login" class="space-y-4">
                                <div>
                                    <label for="email"
                                        class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Email address') }}</label>
                                    <div class="mt-1 relative">
                                        <input wire:model="email" id="email" name="email" type="email"
                                            autocomplete="email" required
                                            class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        @error('email')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="password"
                                        class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Password') }}</label>
                                    <div class="mt-1 relative">
                                        <input wire:model="password" id="password" name="password" type="password"
                                            autocomplete="current-password" required
                                            class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        @error('password')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="flex justify-end mt-1">
                                        <button type="button" wire:click="switchMode('forgot-password')"
                                            class="text-xs font-semibold text-primary-600 hover:text-primary-500">{{ __('Forgot password?') }}</button>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="flex w-full justify-center rounded-xl bg-primary-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all transform hover:-translate-y-0.5">
                                    <span wire:loading.remove wire:target="login">{{ __('Sign in') }}</span>
                                    <span wire:loading wire:target="login">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        @endif

                        @if ($mode === 'register')
                            <form wire:submit.prevent="register" class="space-y-4">
                                <div>
                                    <label for="name"
                                        class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Full Name') }}</label>
                                    <div class="mt-1">
                                        <input wire:model="name" id="name" name="name" type="text"
                                            autocomplete="name" required
                                            class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        @error('name')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="registerEmail"
                                        class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Email address') }}</label>
                                    <div class="mt-1">
                                        <input wire:model="registerEmail" id="registerEmail" name="registerEmail"
                                            type="email" autocomplete="email" required
                                            class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        @error('registerEmail')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="registerPassword"
                                        class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Password') }}</label>
                                    <div class="mt-1">
                                        <input wire:model="registerPassword" id="registerPassword"
                                            name="registerPassword" type="password" required
                                            class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        @error('registerPassword')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="registerPasswordConfirmation"
                                        class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Confirm Password') }}</label>
                                    <div class="mt-1">
                                        <input wire:model="registerPasswordConfirmation"
                                            id="registerPasswordConfirmation" name="registerPasswordConfirmation"
                                            type="password" required
                                            class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300">
                                        <input type="checkbox" wire:model="acceptLegalTerms"
                                            class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span>
                                            {{ __('Acepto los') }}
                                            <a href="{{ route('legal.terms') }}" target="_blank"
                                                class="text-primary-600 hover:text-primary-500 font-semibold">{{ __('Términos de Servicio') }}</a>,
                                            <a href="{{ route('legal.privacy') }}" target="_blank"
                                                class="text-primary-600 hover:text-primary-500 font-semibold">{{ __('Política de Privacidad') }}</a>
                                            {{ __('y la') }}
                                            <a href="{{ route('legal.acceptable-use') }}" target="_blank"
                                                class="text-primary-600 hover:text-primary-500 font-semibold">{{ __('Política de Uso Aceptable') }}</a>.
                                        </span>
                                    </label>
                                    @error('acceptLegalTerms')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="flex w-full justify-center rounded-xl bg-primary-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all transform hover:-translate-y-0.5">
                                    <span wire:loading.remove wire:target="register">{{ __('Create Account') }}</span>
                                    <span wire:loading wire:target="register">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        @endif

                        @if ($mode === 'forgot-password')
                            @if($resetLinkSent)
                                <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                                    <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                                    </svg>
                                    <p class="text-sm text-green-800 dark:text-green-300 font-medium">{{ __('We have sent you a password reset link. Check your inbox.') }}</p>
                                </div>
                            @else
                                <form wire:submit.prevent="sendResetLink" class="space-y-4">
                                    <div>
                                        <label for="forgotEmail"
                                            class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">{{ __('Email address') }}</label>
                                        <div class="mt-1 relative">
                                            <input wire:model="forgotEmail" id="forgotEmail" name="forgotEmail" type="email"
                                                autocomplete="email" required
                                                class="block w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                            @error('forgotEmail')
                                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="flex w-full justify-center rounded-xl bg-primary-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all transform hover:-translate-y-0.5">
                                        <span wire:loading.remove wire:target="sendResetLink">{{ __('Send Reset Link') }}</span>
                                        <span wire:loading wire:target="sendResetLink">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </span>
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if ($mode === 'pending-approval')
                            <div class="text-center">
                                <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                                    <p class="text-sm text-amber-800 dark:text-amber-300 font-medium mb-2">
                                        {{ __('Tu cuenta ha sido creada exitosamente.') }}
                                    </p>
                                    <p class="text-sm text-amber-700 dark:text-amber-400">
                                        {{ __('Un administrador revisará tu solicitud. Recibirás un correo electrónico cuando tu cuenta sea aprobada.') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 text-center text-sm">
                        <p class="text-gray-500 dark:text-gray-400">
                            @if ($mode === 'login')
                                {{ __('Don\'t have an account?') }}
                                <button wire:click="switchMode('register')"
                                    class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">{{ __('Sign up') }}</button>
                            @elseif ($mode === 'forgot-password')
                                <button wire:click="switchMode('login')"
                                    class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">{{ __('Back to Sign in') }}</button>
                            @elseif ($mode === 'pending-approval')
                                <button wire:click="switchMode('login')"
                                    class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">{{ __('Ir al Inicio de Sesión') }}</button>
                            @else
                                {{ __('Already have an account?') }}
                                <button wire:click="switchMode('login')"
                                    class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">{{ __('Sign in') }}</button>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
