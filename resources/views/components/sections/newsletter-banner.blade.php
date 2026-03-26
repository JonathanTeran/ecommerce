@props(['section', 'data' => []])
@php
    $config = $section->config;
    $benefits = $config['benefit_items'] ?? [];
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

<section class="{{ $hasCustomStyle ? '' : 'py-16 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500' }}"
    @if($hasCustomStyle) style="{!! $customCss !!}" @endif
    x-data="{ email: '', submitted: false, loading: false }">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            {{-- Icon --}}
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            {{-- Heading --}}
            <h2 class="text-3xl md:text-4xl font-bold font-heading text-white mb-3">
                {{ __($config['heading'] ?? 'Ofertas exclusivas en tu correo') }}
            </h2>
            <p class="text-lg text-white/80 mb-8 max-w-xl mx-auto">
                {{ __($config['subheading'] ?? 'Suscribete y se el primero en conocer nuestras promociones') }}
            </p>

            {{-- Benefits --}}
            @if(count($benefits) > 0)
                <div class="flex flex-wrap justify-center gap-4 md:gap-6 mb-8">
                    @foreach($benefits as $benefit)
                        <div class="flex items-center gap-2 text-white/90 text-sm">
                            <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ __($benefit) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Form --}}
            <div x-show="!submitted" class="max-w-md mx-auto">
                <form @submit.prevent="loading = true; fetch('/api/newsletter', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({email: email}) }).then(() => { submitted = true; loading = false; }).catch(() => { submitted = true; loading = false; })" class="flex gap-2">
                    <input type="email" x-model="email" required
                        placeholder="{{ __('Tu correo electronico') }}"
                        class="flex-1 px-5 py-3.5 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/25 transition">
                    <button type="submit" :disabled="loading"
                        class="px-6 py-3.5 bg-white text-indigo-700 font-bold rounded-xl hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl active:scale-95 shrink-0 disabled:opacity-70">
                        <span x-show="!loading">{{ __($config['button_text'] ?? 'Suscribirme') }}</span>
                        <span x-show="loading">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </span>
                    </button>
                </form>
                <p class="text-xs text-white/50 mt-3">{{ __('Sin spam. Puedes cancelar en cualquier momento.') }}</p>
            </div>

            {{-- Success State --}}
            <div x-show="submitted" x-cloak class="max-w-md mx-auto">
                <div class="flex items-center justify-center gap-3 bg-white/20 backdrop-blur-sm rounded-xl px-6 py-4 border border-white/30">
                    <svg class="w-6 h-6 text-emerald-300 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-white font-semibold">{{ __('Gracias por suscribirte!') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>
