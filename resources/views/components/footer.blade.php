@php
    $footerConfig = $tenantSettings?->getFooterConfig() ?? ['brand_description' => '', 'columns' => [], 'show_newsletter' => true, 'copyright_text' => '', 'show_payment_icons' => false];
    $socialLinks = $tenantSettings?->getSocialLinks() ?? [];
    $customColumns = collect($footerConfig['columns'] ?? []);
    $useDefaultColumns = $customColumns->isEmpty();
    $brandDescription = $footerConfig['brand_description'] ?? '';
    $copyrightText = $footerConfig['copyright_text'] ?? '';
    $showNewsletter = $footerConfig['show_newsletter'] ?? true;
    $hasSocial = collect($socialLinks)->filter()->isNotEmpty();
@endphp
<footer class="bg-white dark:bg-dark-surface border-t border-slate-100 dark:border-slate-800 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">

            <!-- Brand -->
            <div class="space-y-4">
                <a href="/" class="flex items-center gap-2">
                    @if($tenantSettings?->site_logo)
                        <img src="{{ url('storage/' . $tenantSettings->site_logo) }}" alt="Logo" class="h-10 w-auto">
                    @else
                        <span class="font-heading font-bold text-2xl tracking-tight text-slate-900 dark:text-white">
                            {{ $tenantSettings?->site_name ?? config('app.name') }}
                        </span>
                    @endif
                </a>
                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                    @if(!empty($brandDescription))
                        {{ $brandDescription }}
                    @else
                        {{ __('Elevando tu experiencia de compra con productos premium y servicio excepcional. Donde la calidad encuentra el lujo.') }}
                    @endif
                </p>
                <div class="flex gap-4">
                    @if(!empty($socialLinks['facebook_url']))
                        <a href="{{ $socialLinks['facebook_url'] }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">Facebook</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg></a>
                    @endif
                    @if(!empty($socialLinks['instagram_url']))
                        <a href="{{ $socialLinks['instagram_url'] }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">Instagram</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465 1.067-.047 1.407-.06 3.808-.06h.63zm1.673 5.378a1.296 1.296 0 11-1.884-1.884 1.296 1.296 0 011.884 1.884zm-2.597 1.196a5.378 5.378 0 110 10.756 5.378 5.378 0 010-10.756z" clip-rule="evenodd" /></svg></a>
                    @endif
                    @if(!empty($socialLinks['tiktok_url']))
                        <a href="{{ $socialLinks['tiktok_url'] }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">TikTok</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></a>
                    @endif
                    @if(!empty($socialLinks['twitter_url']))
                        <a href="{{ $socialLinks['twitter_url'] }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">X</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                    @endif
                    @if(!empty($socialLinks['youtube_url']))
                        <a href="{{ $socialLinks['youtube_url'] }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">YouTube</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.418-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" /></svg></a>
                    @endif
                    @if(!empty($socialLinks['whatsapp_number']))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $socialLinks['whatsapp_number']) }}" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">WhatsApp</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>
                    @endif
                    @if(!$hasSocial)
                        {{-- Default placeholder social icons when nothing is configured --}}
                        <a href="#" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">Facebook</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg></a>
                        <a href="#" class="text-slate-400 hover:text-primary-500 transition-colors"><span class="sr-only">Instagram</span><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465 1.067-.047 1.407-.06 3.808-.06h.63zm1.673 5.378a1.296 1.296 0 11-1.884-1.884 1.296 1.296 0 011.884 1.884zm-2.597 1.196a5.378 5.378 0 110 10.756 5.378 5.378 0 010-10.756z" clip-rule="evenodd" /></svg></a>
                    @endif
                </div>
            </div>

            @if($useDefaultColumns)
                <!-- Default Shop Column -->
                <div>
                    <h3 class="font-heading font-bold text-slate-900 dark:text-white mb-6">{{ __('Tienda') }}</h3>
                    <ul class="space-y-4">
                        <li><a href="{{ route('shop.index') }}?sort=latest" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Nuevos Lanzamientos') }}</a></li>
                        <li><a href="{{ route('shop.index') }}?sort=popular" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Más Vendidos') }}</a></li>
                        <li><a href="{{ route('shop.index') }}" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Todos los Productos') }}</a></li>
                    </ul>
                </div>

                <!-- Default Support Column -->
                <div>
                    <h3 class="font-heading font-bold text-slate-900 dark:text-white mb-6">{{ __('Soporte') }}</h3>
                    <ul class="space-y-4">
                        <li><a href="{{ route('about') }}" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Nosotros') }}</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Términos de Servicio') }}</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Política de Privacidad') }}</a></li>
                        <li><a href="{{ route('legal.acceptable-use') }}" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ __('Política de Uso Aceptable') }}</a></li>
                    </ul>
                </div>
            @else
                @foreach($customColumns as $column)
                    <div>
                        <h3 class="font-heading font-bold text-slate-900 dark:text-white mb-6">{{ $column['title'] }}</h3>
                        <ul class="space-y-4">
                            @foreach($column['links'] ?? [] as $link)
                                <li><a href="{{ $link['url'] }}" class="text-slate-600 dark:text-slate-400 hover:text-primary-500 transition-colors text-sm">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif

            @if($showNewsletter)
            <!-- Newsletter -->
            <div x-data="{ email: '', loading: false, success: '', error: '' }">
                <h3 class="font-heading font-bold text-slate-900 dark:text-white mb-6">{{ __('Mantente Actualizado') }}</h3>
                <p class="text-slate-600 dark:text-slate-400 text-sm mb-4">
                    {{ __('Suscríbete a nuestro boletín para ofertas exclusivas y novedades.') }}</p>
                <form class="flex gap-2" @submit.prevent="
                    loading = true; success = ''; error = '';
                    fetch('/api/newsletter/subscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                        body: JSON.stringify({ email })
                    })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok) { success = data.message; email = ''; }
                        else { error = data.message || data.errors?.email?.[0] || 'Error al suscribirse.'; }
                    })
                    .catch(() => { error = 'Error de conexion.'; })
                    .finally(() => { loading = false; })
                ">
                    <input type="email" x-model="email" required placeholder="{{ __('Tu correo electrónico') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-primary-500 dark:text-white placeholder-slate-400">
                    <button type="submit" :disabled="loading"
                        class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-3 rounded-lg hover:opacity-90 transition-opacity">
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                        <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </form>
                <p x-show="success" x-text="success" class="text-green-600 dark:text-green-400 text-sm mt-2" x-cloak></p>
                <p x-show="error" x-text="error" class="text-red-600 dark:text-red-400 text-sm mt-2" x-cloak></p>
            </div>
            @endif

        </div>

        <div
            class="border-t border-slate-100 dark:border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-slate-500 text-sm">
                @if(!empty($copyrightText))
                    {{ $copyrightText }}
                @else
                    &copy; {{ date('Y') }} {{ $tenantSettings?->site_name ?? config('app.name') }}.
                    {{ __('Todos los derechos reservados.') }}
                @endif
            </p>
            @if($footerConfig['show_payment_icons'] ?? false)
            <div class="flex gap-4">
                <span class="text-slate-400 text-xs">Visa</span>
                <span class="text-slate-400 text-xs">Mastercard</span>
                <span class="text-slate-400 text-xs">PayPal</span>
            </div>
            @endif
        </div>
    </div>
</footer>
