<x-layouts.app>
    <div class="bg-slate-50 dark:bg-zinc-950 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-10 space-y-3">
                <p class="text-sm font-semibold text-primary-600">{{ __('Normativa SaaS') }}</p>
                <h1 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 dark:text-white">
                    {{ __('Política de Privacidad') }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Versión') }} {{ $policyVersion }} · {{ __('Vigente desde') }} {{ $effectiveDate }} ·
                    {{ __('Jurisdicción') }}: {{ $jurisdiction }} · {{ __('Cobertura') }}: {{ $serviceRegion }}
                </p>
            </header>

            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-8 space-y-8">
                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('1. Responsable del tratamiento') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __(':company actúa como responsable del tratamiento de datos personales recabados en la plataforma SaaS.', ['company' => $companyName]) }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('2. Datos que tratamos') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Podemos tratar datos de identificación, contacto, facturación, trazas de seguridad (IP, agente de usuario) y registros de actividad necesarios para operar y proteger el sistema.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('3. Finalidades') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Los datos se usan para autenticación, prestación del servicio, procesamiento de transacciones, soporte, prevención de fraude, cumplimiento normativo y mejora continua de la seguridad.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('4. Base legal') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('El tratamiento se fundamenta en la ejecución contractual, cumplimiento de obligaciones legales e interés legítimo en proteger infraestructura y operaciones.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('5. Conservación y seguridad') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Aplicamos medidas técnicas y organizativas razonables para limitar accesos no autorizados, pérdida o alteración. La conservación se limita al tiempo necesario según finalidad y exigencia legal.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('6. Encargados y transferencias') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Podremos usar proveedores tecnológicos para hosting, correo, pagos y monitoreo, bajo acuerdos de confidencialidad y protección de datos acordes a la regulación aplicable.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('7. Derechos del titular') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('El titular puede solicitar acceso, actualización, rectificación y supresión de sus datos, así como oponerse a tratamientos no obligatorios, mediante solicitud al contacto legal indicado.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('8. Transferencias internacionales de datos') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Por la naturaleza global del servicio, los datos pueden procesarse en diferentes países mediante proveedores autorizados, aplicando salvaguardas contractuales y técnicas razonables para mantener un nivel adecuado de protección.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('9. Derechos regionales y normas imperativas') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Si una regulación local otorga derechos adicionales al titular (por ejemplo, privacidad del consumidor o requisitos de transparencia específicos), dichos derechos se aplicarán en la medida exigida por esa normativa. :notice', ['notice' => $consumerProtectionNotice]) }}
                    </p>
                </section>

                <section class="border-t border-slate-200 dark:border-zinc-800 pt-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Solicitudes de privacidad: :email', ['email' => $contactEmail]) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
