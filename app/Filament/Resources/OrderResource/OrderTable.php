<?php

namespace App\Filament\Resources\OrderResource;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class OrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'tenant']))
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Número de Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge(),
                Tables\Columns\TextColumn::make('sri_authorization_status')
                    ->label('Estado SRI')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'authorized' => 'AUTORIZADO',
                        'pending' => 'EN PROCESO',
                        'rejected' => 'RECHAZADO',
                        default => 'SIN ESTADO',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'authorized' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('sri_access_key')
                    ->label('Clave SRI')
                    ->copyable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->state(fn (Order $record) => $record->total),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Estado de Pago')
                    ->options(PaymentStatus::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('print_shipping_label')
                        ->label('Imprimir Etiqueta de Envío')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->url(fn (Order $record) => route('order.shipping-label', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Order $record) => $record->shipping_address !== null),
                    Tables\Actions\Action::make('print_invoice')
                        ->label('Imprimir Factura')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->url(fn (Order $record) => route('order.invoice', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('refund')
                        ->label('Reembolsar')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->visible(fn (Order $record): bool => $record->payment_status === PaymentStatus::COMPLETED)
                        ->requiresConfirmation()
                        ->modalHeading('Procesar Reembolso')
                        ->form([
                            Forms\Components\Select::make('refund_type')
                                ->label('Tipo de Reembolso')
                                ->options([
                                    'full' => 'Reembolso Total',
                                    'partial' => 'Reembolso Parcial',
                                ])
                                ->required()
                                ->live()
                                ->default('full'),
                            Forms\Components\TextInput::make('refund_amount')
                                ->label('Monto a Reembolsar')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->visible(fn (Forms\Get $get): bool => $get('refund_type') === 'partial')
                                ->maxValue(fn (Order $record): float => (float) $record->total),
                            Forms\Components\Textarea::make('refund_reason')
                                ->label('Razón del Reembolso')
                                ->required()
                                ->rows(2),
                        ])
                        ->action(function (Order $record, array $data): void {
                            $amount = $data['refund_type'] === 'full'
                                ? (float) $record->total
                                : (float) $data['refund_amount'];

                            $isFullRefund = $amount >= (float) $record->total;

                            Payment::create([
                                'tenant_id' => $record->tenant_id,
                                'order_id' => $record->id,
                                'transaction_id' => 'REF-'.strtoupper(substr(md5(uniqid()), 0, 10)),
                                'gateway' => 'manual',
                                'method' => 'refund',
                                'amount' => -$amount,
                                'currency' => $record->currency ?? 'USD',
                                'status' => PaymentStatus::REFUNDED,
                                'refunded_amount' => $amount,
                                'refunded_at' => now(),
                                'paid_at' => now(),
                                'gateway_response' => [
                                    'type' => $data['refund_type'],
                                    'reason' => $data['refund_reason'],
                                    'original_total' => $record->total,
                                ],
                            ]);

                            $record->update([
                                'payment_status' => $isFullRefund
                                    ? PaymentStatus::REFUNDED
                                    : PaymentStatus::PARTIALLY_REFUNDED,
                            ]);

                            if ($isFullRefund) {
                                $record->updateStatus(
                                    \App\Enums\OrderStatus::REFUNDED,
                                    'Reembolso total: $'.number_format($amount, 2).' - '.$data['refund_reason']
                                );
                            }

                            Notification::make()
                                ->title('Reembolso procesado')
                                ->body('$'.number_format($amount, 2).' reembolsado exitosamente')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('sri_authorize')
                        ->label('SRI Autorizar')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('warning')
                        ->action(function (Order $record, \App\Services\SriService $sriService) {
                            try {
                                // 1. Generate Access Key
                                $accessKey = $sriService->generateAccessKey($record);
                                $record->update(['sri_access_key' => $accessKey]);

                                // 2. Generate XML
                                $xml = $sriService->generateInvoiceXml($record, $accessKey);

                                // 3. Sign XML
                                try {
                                    $xmlSigned = $sriService->signXml($xml);
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Error Firma')
                                        ->body('No se pudo firmar el XML: '.$e->getMessage())
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                // 4. Save XML
                                $path = 'xml/facturas/'.$accessKey.'.xml';
                                Storage::put($path, $xmlSigned);
                                $record->update(['sri_xml_path' => $path]);

                                // 5. Send to SRI (Recepcion)
                                $response = $sriService->sendToRecepcion($xmlSigned);

                                if (! $sriService->shouldAttemptAuthorization($response)) {
                                    $errorMessage = $response['message'] ?? 'Sin mensaje';
                                    $errors = $response['errors'] ?? null;
                                    if (is_array($errors) && $errors !== []) {
                                        $errorMessage .= ' | '.implode(' | ', array_slice($errors, 0, 3));
                                    }

                                    $source = $response['source'] ?? null;
                                    $validation = $response['validation'] ?? null;
                                    $sourceLabel = match ($source) {
                                        'offline' => 'VALIDACION OFFLINE',
                                        'system' => 'SISTEMA',
                                        default => 'SRI',
                                    };

                                    if ($source === 'offline' && is_string($validation) && $validation !== '') {
                                        $sourceLabel .= ' ('.$validation.')';
                                    }

                                    $errorMessage = $sourceLabel.': '.$errorMessage;

                                    Notification::make()
                                        ->title('SRI Rechazado')
                                        ->body('Estado: '.$response['status'].' - '.$errorMessage)
                                        ->danger()
                                        ->send();
                                    $record->update([
                                        'sri_authorization_status' => 'rejected',
                                        'sri_error_message' => $errorMessage,
                                    ]);

                                    return;
                                }

                                // 6. Check Authorization (Wait a bit or run immediately?)
                                sleep(2); // Simple delay
                                $authResponse = $sriService->authorize($accessKey);

                                if ($authResponse['status'] === 'AUTORIZADO') {
                                    $update = [
                                        'sri_authorization_status' => 'authorized',
                                        'sri_authorization_date' => $authResponse['date'] ?? now(),
                                        'sri_authorization_number' => $authResponse['authorization_number'],
                                        'sri_error_message' => null,
                                    ];

                                    if (! empty($authResponse['xml'])) {
                                        $update['sri_authorized_xml_path'] = $sriService->storeAuthorizedXml($record, $authResponse['xml']);
                                    }

                                    $record->update($update);

                                    Notification::make()
                                        ->title('SRI Autorizado')
                                        ->body('Número: '.$authResponse['authorization_number'])
                                        ->success()
                                        ->send();
                                } elseif ($authResponse['status'] === 'NO AUTORIZADO') {
                                    $message = ($authResponse['source'] ?? null) === 'system'
                                        ? 'SISTEMA: '.$authResponse['message']
                                        : 'SRI: '.$authResponse['message'];

                                    $record->update([
                                        'sri_authorization_status' => 'rejected',
                                        'sri_error_message' => $message,
                                    ]);
                                    Notification::make()
                                        ->title('SRI Rechazado')
                                        ->body($message)
                                        ->danger()
                                        ->send();
                                } else {
                                    $message = ($authResponse['source'] ?? null) === 'system'
                                        ? 'SISTEMA: '.$authResponse['message']
                                        : 'SRI: '.$authResponse['message'];

                                    $record->update([
                                        'sri_authorization_status' => 'pending',
                                        'sri_error_message' => $message,
                                    ]);
                                    $sriService->scheduleAuthorizationCheck($record);
                                    Notification::make()
                                        ->title('SRI Pendiente')
                                        ->body('Enviado pero no autorizado: '.$message)
                                        ->warning()
                                        ->send();
                                }

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error Sistema')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Order $record) => $record->sri_authorization_status !== 'authorized'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
