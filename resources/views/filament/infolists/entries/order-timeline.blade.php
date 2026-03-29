@php
    $record = $getRecord();
    $history = $record->statusHistory()->with('user')->get();

    $statusConfig = [
        'pending' => ['label' => 'Pendiente', 'color' => '#f59e0b', 'icon' => 'clock'],
        'processing' => ['label' => 'En Proceso', 'color' => '#3b82f6', 'icon' => 'cog'],
        'shipped' => ['label' => 'Enviado', 'color' => '#8b5cf6', 'icon' => 'truck'],
        'delivered' => ['label' => 'Entregado', 'color' => '#10b981', 'icon' => 'check-circle'],
        'completed' => ['label' => 'Completado', 'color' => '#10b981', 'icon' => 'check-circle'],
        'cancelled' => ['label' => 'Cancelado', 'color' => '#ef4444', 'icon' => 'x-circle'],
        'refunded' => ['label' => 'Reembolsado', 'color' => '#f97316', 'icon' => 'arrow-uturn-left'],
    ];
@endphp

<div class="space-y-0">
    @forelse($history as $event)
        @php
            $config = $statusConfig[$event->new_status] ?? ['label' => ucfirst($event->new_status), 'color' => '#6b7280', 'icon' => 'ellipsis-horizontal'];
            $oldConfig = $statusConfig[$event->old_status] ?? ['label' => ucfirst($event->old_status ?? 'Nuevo'), 'color' => '#9ca3af', 'icon' => 'ellipsis-horizontal'];
        @endphp
        <div class="relative flex gap-4 pb-8 last:pb-0">
            {{-- Vertical line --}}
            @if(!$loop->last)
                <div class="absolute left-4 top-8 bottom-0 w-0.5" style="background-color: {{ $config['color'] }}20;"></div>
            @endif

            {{-- Circle indicator --}}
            <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full" style="background-color: {{ $config['color'] }}15; border: 2px solid {{ $config['color'] }};">
                <svg class="h-4 w-4" style="color: {{ $config['color'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    @switch($config['icon'])
                        @case('clock')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @break
                        @case('cog')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93s.844.126 1.186-.109l.758-.553a1.125 1.125 0 011.597.22l.77.935a1.125 1.125 0 01-.22 1.597l-.553.758a1.125 1.125 0 00-.109 1.186c.166.396.506.71.93.78l.894.149c.542.09.94.56.94 1.109v1.094c0 .55-.398 1.02-.94 1.11l-.894.149a1.125 1.125 0 00-.93.78 1.125 1.125 0 00.109 1.186l.553.758a1.125 1.125 0 01-.22 1.597l-.935.77a1.125 1.125 0 01-1.597-.22l-.758-.553a1.125 1.125 0 00-1.186-.109 1.125 1.125 0 00-.78.93l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894a1.125 1.125 0 00-.78-.93 1.125 1.125 0 00-1.186.109l-.758.553a1.125 1.125 0 01-1.597-.22l-.77-.935a1.125 1.125 0 01.22-1.597l.553-.758c.235-.323.302-.767.109-1.186a1.125 1.125 0 00-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.166-.396.126-.843-.109-1.186l-.553-.758a1.125 1.125 0 01.22-1.597l.935-.77a1.125 1.125 0 011.597.22l.758.553c.323.235.767.302 1.186.109.396-.166.71-.506.78-.93l.15-.894z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            @break
                        @case('truck')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            @break
                        @case('check-circle')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @break
                        @case('x-circle')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @break
                        @case('arrow-uturn-left')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                            @break
                        @default
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    @endswitch
                </svg>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background-color: {{ $config['color'] }}15; color: {{ $config['color'] }};">
                        {{ $config['label'] }}
                    </span>
                    @if($event->old_status)
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            desde {{ $oldConfig['label'] }}
                        </span>
                    @endif
                </div>
                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $event->created_at->format('d/m/Y H:i') }}</span>
                    @if($event->user)
                        <span>por {{ $event->user->name }}</span>
                    @endif
                </div>
                @if($event->notes)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $event->notes }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="flex items-center gap-3 rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sin historial de cambios</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">Pedido creado {{ $record->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    @endforelse
</div>
