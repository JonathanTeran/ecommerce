<div>
    @php
        $settings = \App\Models\GeneralSetting::first();
        $logo = $settings?->site_logo;
    @endphp

    @if ($logo)
        <div class="mb-4 p-4 border rounded-lg bg-gray-50">
            <div class="flex items-center gap-4 mb-3">
                <img src="{{ url('storage/' . $logo) }}" alt="Logo" class="max-h-16 border rounded p-2 bg-white">
                <div class="text-sm text-gray-600 flex-1">
                    <div class="font-medium">{{ basename($logo) }}</div>
                    <div class="text-xs text-gray-500">{{ $logo }}</div>
                </div>
                <a href="{{ route('admin.upload-logos') }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Cambiar Logo
                </a>
            </div>
        </div>
    @else
        <div class="mb-4 p-4 border border-dashed rounded-lg bg-gray-50 text-center">
            <p class="text-sm text-gray-500 italic mb-3">No hay logo cargado</p>
            <a href="{{ route('admin.upload-logos') }}" target="_blank"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Subir Logo
            </a>
        </div>
    @endif
</div>
