<x-layouts.app>
    <div class="bg-slate-50 dark:bg-zinc-950 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-10 space-y-3">
                <p class="text-sm font-semibold text-primary-600">{{ __('Normativa SaaS') }}</p>
                <h1 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 dark:text-white">
                    {{ __('Política de Uso Aceptable') }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Versión') }} {{ $policyVersion }} · {{ __('Vigente desde') }} {{ $effectiveDate }} ·
                    {{ __('Jurisdicción') }}: {{ $jurisdiction }} · {{ __('Cobertura') }}: {{ $serviceRegion }}
                </p>
            </header>

            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-8 space-y-8">
                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('1. Principio general') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('La plataforma debe utilizarse de forma lícita, ética y segura, evitando cualquier acción que afecte disponibilidad, integridad o confidencialidad del servicio.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('2. Actividades prohibidas') }}</h2>
                    <ul class="list-disc pl-6 text-slate-600 dark:text-slate-300 space-y-2">
                        <li>{{ __('Acceso no autorizado a cuentas, APIs, bases de datos o infraestructura.') }}</li>
                        <li>{{ __('Pruebas de intrusión, escaneo o scraping masivo sin autorización expresa.') }}</li>
                        <li>{{ __('Distribución de malware, phishing, spam o contenido fraudulento.') }}</li>
                        <li>{{ __('Uso que viole propiedad intelectual, derechos de terceros o normativa vigente.') }}</li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('3. Controles de seguridad') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Se prohíbe eludir mecanismos de autenticación, cifrado, límites de uso o controles antifraude. Cualquier incidente debe reportarse de inmediato.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('4. Ejecución y sanciones') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('El incumplimiento puede causar bloqueo de sesiones, suspensión temporal, terminación definitiva de cuenta y acciones legales cuando corresponda.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('5. Conservación de evidencia') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Para fines de seguridad y cumplimiento, el sistema puede conservar registros técnicos y de aceptación normativa vinculados a actividades críticas.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('6. Sanciones y cumplimiento internacional') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('No se permite el uso de la plataforma para evadir sanciones, controles de exportación o restricciones comerciales internacionales. :notice', ['notice' => $restrictedCountriesNotice]) }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('7. Infracciones de propiedad intelectual') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('El uso o carga de contenidos que infrinjan derechos de autor, marcas, patentes, diseños o secretos empresariales podrá generar retiro inmediato del contenido, suspensión de cuenta y colaboración con autoridades cuando aplique.') }}
                    </p>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Reporte de infracción: :email. Tiempo estimado de revisión inicial: hasta :days días.', ['email' => $ipInfringementContactEmail, 'days' => $ipTakedownResponseDays]) }}
                    </p>
                </section>

                <section class="border-t border-slate-200 dark:border-zinc-800 pt-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Reportes de abuso: :email', ['email' => $contactEmail]) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
