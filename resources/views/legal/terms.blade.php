<x-layouts.app>
    <div class="bg-slate-50 dark:bg-zinc-950 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-10 space-y-3">
                <p class="text-sm font-semibold text-primary-600">{{ __('Normativa SaaS') }}</p>
                <h1 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 dark:text-white">
                    {{ __('Términos de Servicio SaaS') }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Versión') }} {{ $policyVersion }} · {{ __('Vigente desde') }} {{ $effectiveDate }} ·
                    {{ __('Jurisdicción') }}: {{ $jurisdiction }} · {{ __('Cobertura') }}: {{ $serviceRegion }}
                </p>
            </header>

            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-8 space-y-8">
                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('1. Alcance del servicio') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Este acuerdo regula el uso de la plataforma SaaS administrada por :company. El acceso al sistema implica aceptación total de estos términos y de las políticas vinculadas.', ['company' => $companyName]) }}
                    </p>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Sitio oficial del administrador:') }}
                        <a href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer"
                            class="font-semibold text-primary-600 hover:text-primary-500">
                            {{ $companyWebsite }}
                        </a>
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('2. Licencia de uso') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Se concede una licencia limitada, revocable, no exclusiva e intransferible para usar la plataforma únicamente para fines comerciales legítimos del cliente.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('3. Seguridad de cuenta') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Cada cliente es responsable de custodiar credenciales, definir permisos internos y notificar accesos no autorizados. Toda operación realizada con la cuenta se presume válida salvo prueba en contrario.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('4. Uso prohibido') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Queda prohibido usar la plataforma para actividades ilícitas, intento de intrusión, distribución de malware, ingeniería inversa, extracción masiva no autorizada o vulneración de derechos de terceros.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('5. Datos, privacidad y confidencialidad') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('El tratamiento de datos se rige por la Política de Privacidad. Las partes se obligan a mantener confidencialidad sobre información técnica, comercial y personal a la que accedan durante la relación contractual.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('6. Disponibilidad y mantenimiento') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('El servicio puede interrumpirse temporalmente por mantenimiento, seguridad o fuerza mayor. Se adoptarán medidas razonables para preservar continuidad, integridad y trazabilidad operativa.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('7. Pagos, suspensión y terminación') }}</h2>
                    <ul class="list-disc pl-6 text-slate-600 dark:text-slate-300 space-y-2 leading-relaxed">
                        <li>
                            {{ __('La suscripción se factura por adelantado y cada factura debe pagarse en su fecha de vencimiento.') }}
                        </li>
                        <li>
                            {{ __('Si existe mora, se concede un periodo de gracia de :days días corridos contados desde la fecha de vencimiento.', ['days' => $billingGracePeriodDays]) }}
                        </li>
                        <li>
                            {{ __('Si no se regulariza dentro de ese periodo, la cuenta podrá ser deshabilitada automáticamente al día :days (por ejemplo: vencimiento 1 de abril de 2026, deshabilitación el 1 de mayo de 2026).', ['days' => $billingGracePeriodDays]) }}
                        </li>
                        <li>
                            {{ __('Una vez confirmado el pago, el servicio se reactiva en un plazo estimado de hasta :hours horas.', ['hours' => $billingReactivationHours]) }}
                        </li>
                        <li>
                            {{ __('Si el impago supera :days días corridos, podrá ejecutarse terminación contractual y cierre de cuenta.', ['days' => $billingTerminationDays]) }}
                        </li>
                        <li>
                            {{ __('Tras la terminación, los datos podrán conservarse hasta :days días por cumplimiento legal, auditoría y resolución de disputas.', ['days' => $billingDataRetentionDays]) }}
                        </li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('8. Limitación de responsabilidad') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('En la máxima medida permitida por la ley, la responsabilidad total derivada del servicio se limita al valor efectivamente pagado por el cliente en el periodo contractual vigente.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('9. Cambios normativos') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Las políticas pueden actualizarse para cumplir requisitos legales o de seguridad. La versión vigente se publica en este sitio y su aceptación podrá requerirse nuevamente en operaciones críticas.') }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('10. Operación global y jurisdicciones restringidas') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('La plataforma se ofrece globalmente, sujeta a restricciones de exportación, sanciones económicas y cumplimiento regulatorio aplicable. :notice', ['notice' => $restrictedCountriesNotice]) }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('11. Precios, impuestos y moneda') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        @if ($pricesIncludeTaxes)
                            {{ __('Los precios publicados incluyen los impuestos aplicables, salvo indicación expresa distinta para una jurisdicción específica.') }}
                        @else
                            {{ __('Los precios publicados no incluyen necesariamente impuestos locales (IVA, GST, sales tax u otros), los cuales podrán calcularse y cobrarse según país o región del cliente.') }}
                        @endif
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('12. Ley aplicable y resolución de disputas') }}</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Este acuerdo se rige por: :law. Las controversias se resolverán mediante: :dispute. :consumer', ['law' => $governingLaw, 'dispute' => $disputeResolution, 'consumer' => $consumerProtectionNotice]) }}
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('13. Propiedad intelectual') }}</h2>
                    <ul class="list-disc pl-6 text-slate-600 dark:text-slate-300 space-y-2 leading-relaxed">
                        <li>
                            {{ __('La plataforma, su código fuente, diseño, marca, interfaces, documentación y elementos distintivos son propiedad de :owner o sus licenciantes.', ['owner' => $ipOwnerName]) }}
                        </li>
                        <li>
                            {{ __('El cliente mantiene titularidad sobre sus datos y contenidos, y otorga únicamente las autorizaciones necesarias para hospedaje, respaldo, procesamiento y operación del servicio.') }}
                        </li>
                        <li>
                            {{ __('Salvo autorización escrita, queda prohibida la copia, descompilación, ingeniería inversa, sublicenciamiento o reutilización no permitida de componentes protegidos.') }}
                        </li>
                        <li>
                            {{ __('Si se detecta posible infracción de derechos de autor o marca, puede notificarse a :email y se atenderá en un plazo razonable de hasta :days días.', ['email' => $ipInfringementContactEmail, 'days' => $ipTakedownResponseDays]) }}
                        </li>
                    </ul>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('Titular y sitio oficial de propiedad intelectual:') }}
                        <a href="{{ $ipOwnerWebsite }}" target="_blank" rel="noopener noreferrer"
                            class="font-semibold text-primary-600 hover:text-primary-500">
                            {{ $ipOwnerWebsite }}
                        </a>
                    </p>
                </section>

                <section class="border-t border-slate-200 dark:border-zinc-800 pt-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Contacto legal: :email', ['email' => $contactEmail]) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
