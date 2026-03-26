<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ManageSriSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Facturación Electrónica SRI';

    protected static ?string $navigationLabel = 'Facturación SRI';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.manage-sri-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return false;
        }

        return $tenant->hasModule(Module::SriInvoicing);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $settings = GeneralSetting::forCurrentTenantOrCreate([
            'sri_environment' => 1,
            'sri_establishment_code' => '001',
            'sri_emission_point_code' => '001',
            'sri_next_sequence' => 1,
        ]);

        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Contribuyente')
                    ->description('Información fiscal requerida por el SRI para la emisión de comprobantes electrónicos.')
                    ->schema([
                        Forms\Components\TextInput::make('sri_ruc')
                            ->label('RUC')
                            ->helperText('Registro Único de Contribuyentes (13 dígitos)')
                            ->required()
                            ->maxLength(13)
                            ->minLength(13)
                            ->numeric()
                            ->placeholder('0999999999001'),
                        Forms\Components\TextInput::make('sri_company_name')
                            ->label('Razón Social')
                            ->helperText('Nombre legal del contribuyente tal como aparece en el RUC')
                            ->required()
                            ->maxLength(300),
                        Forms\Components\TextInput::make('sri_commercial_name')
                            ->label('Nombre Comercial')
                            ->helperText('Nombre comercial del negocio (puede ser diferente a la razón social)')
                            ->maxLength(300),
                        Forms\Components\TextInput::make('sri_establishment_address')
                            ->label('Dirección del Establecimiento')
                            ->helperText('Dirección matriz registrada en el SRI')
                            ->maxLength(300),
                        Forms\Components\TextInput::make('sri_contribution_type')
                            ->label('Tipo de Contribuyente')
                            ->helperText('Ej: CONTRIBUYENTE RÉGIMEN RIMPE')
                            ->maxLength(100),
                        Forms\Components\Toggle::make('sri_accounting_required')
                            ->label('Obligado a llevar contabilidad')
                            ->helperText('Marcar si el SRI lo designa como obligado a llevar contabilidad'),
                    ])->columns(2),

                Forms\Components\Section::make('Puntos de Emisión y Secuencia')
                    ->description('Configuración del establecimiento y punto de emisión para la numeración de comprobantes.')
                    ->schema([
                        Forms\Components\TextInput::make('sri_establishment_code')
                            ->label('Código de Establecimiento')
                            ->helperText('Código de 3 dígitos del establecimiento (ej: 001)')
                            ->required()
                            ->maxLength(3)
                            ->minLength(3)
                            ->default('001')
                            ->placeholder('001'),
                        Forms\Components\TextInput::make('sri_emission_point_code')
                            ->label('Código de Punto de Emisión')
                            ->helperText('Código de 3 dígitos del punto de emisión (ej: 001)')
                            ->required()
                            ->maxLength(3)
                            ->minLength(3)
                            ->default('001')
                            ->placeholder('001'),
                        Forms\Components\TextInput::make('sri_next_sequence')
                            ->label('Secuencia Siguiente')
                            ->helperText('Número de la próxima factura a emitir. Se incrementa automáticamente.')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])->columns(3),

                Forms\Components\Section::make('Firma Electrónica')
                    ->description('Certificado digital .p12 emitido por una entidad certificadora autorizada por el BCE.')
                    ->schema([
                        Forms\Components\FileUpload::make('sri_signature_file')
                            ->label('Archivo de Firma Electrónica (.p12)')
                            ->helperText('Sube tu archivo de firma electrónica en formato .p12 o .pfx')
                            ->disk('local')
                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/sri')
                            ->acceptedFileTypes(['application/x-pkcs12', '.p12', '.pfx'])
                            ->maxSize(5120)
                            ->preserveFilenames(),
                        Forms\Components\TextInput::make('sri_signature_password')
                            ->label('Clave de Firma Electrónica')
                            ->helperText('Contraseña del archivo .p12')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (?string $state, $record) => $state ?: ($record?->sri_signature_password ?? null)),
                        Forms\Components\Placeholder::make('sri_signature_info')
                            ->label('Estado de la Firma')
                            ->content(function () {
                                $settings = GeneralSetting::forCurrentTenant();

                                if (! $settings?->sri_signature_path) {
                                    return 'No hay firma electrónica configurada.';
                                }

                                $info = 'Archivo: ' . basename($settings->sri_signature_path);

                                if ($settings->sri_signature_valid_from) {
                                    $info .= ' | Válida desde: ' . $settings->sri_signature_valid_from->format('d/m/Y');
                                }

                                if ($settings->sri_signature_expires_at) {
                                    $expired = $settings->sri_signature_expires_at->isPast();
                                    $info .= ' | Expira: ' . $settings->sri_signature_expires_at->format('d/m/Y');
                                    if ($expired) {
                                        $info .= ' (EXPIRADA)';
                                    }
                                }

                                return $info;
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Ambiente SRI')
                    ->description('Selecciona el ambiente de conexión con el SRI.')
                    ->schema([
                        Forms\Components\Select::make('sri_environment')
                            ->label('Ambiente')
                            ->helperText('Pruebas para testing, Producción para facturas reales')
                            ->options([
                                1 => 'Pruebas (Testing)',
                                2 => 'Producción',
                            ])
                            ->required()
                            ->default(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = GeneralSetting::forCurrentTenantOrCreate();

        if (isset($data['sri_signature_file']) && $data['sri_signature_file']) {
            $filePath = $data['sri_signature_file'];

            if (is_string($filePath)) {
                $data['sri_signature_path'] = storage_path('app/' . $filePath);
            }

            if (! empty($data['sri_signature_password']) && $data['sri_signature_path']) {
                $this->validateSignature($data['sri_signature_path'], $data['sri_signature_password']);
            }
        }

        unset($data['sri_signature_file'], $data['sri_signature_info']);

        $settings->update($data);

        Notification::make()
            ->title('Configuración SRI guardada exitosamente')
            ->success()
            ->send();
    }

    protected function validateSignature(string $path, string $password): void
    {
        if (! file_exists($path)) {
            return;
        }

        $p12Content = file_get_contents($path);
        $certs = [];

        if (! openssl_pkcs12_read($p12Content, $certs, $password)) {
            Notification::make()
                ->title('Error al validar la firma electrónica')
                ->body('No se pudo abrir el archivo .p12 con la clave proporcionada. Verifica que la clave sea correcta.')
                ->danger()
                ->send();

            return;
        }

        $cert = openssl_x509_parse($certs['cert']);

        if ($cert) {
            $settings = GeneralSetting::forCurrentTenantOrCreate();
            $settings->update([
                'sri_signature_valid_from' => isset($cert['validFrom_time_t']) ? date('Y-m-d H:i:s', $cert['validFrom_time_t']) : null,
                'sri_signature_expires_at' => isset($cert['validTo_time_t']) ? date('Y-m-d H:i:s', $cert['validTo_time_t']) : null,
            ]);

            Notification::make()
                ->title('Firma electrónica validada correctamente')
                ->body('Certificado válido hasta: ' . date('d/m/Y', $cert['validTo_time_t']))
                ->success()
                ->send();
        }
    }
}
