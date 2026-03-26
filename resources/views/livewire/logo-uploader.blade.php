<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6">Upload Company Logos</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Logo Upload -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-3">System Logo</h3>

        @if ($currentLogo)
            <div class="mb-4">
                <img src="{{ url('storage/' . $currentLogo) }}" alt="Current Logo" class="max-w-xs border p-2">
                <p class="text-sm text-gray-600 mt-2">Current: {{ $currentLogo }}</p>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <input type="file" wire:model="logo" accept="image/*"
                class="block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded file:border-0
                file:text-sm file:font-semibold
                file:bg-indigo-50 file:text-indigo-700
                hover:file:bg-indigo-100">

            @if ($logo)
                <button wire:click="uploadLogo" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Upload Logo
                </button>
            @endif
        </div>

        @error('logo')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror

        @if ($logo)
            <div class="mt-2 text-sm text-gray-600">
                Preview: {{ $logo->getClientOriginalName() }}
            </div>
        @endif
    </div>

    <!-- Favicon Upload -->
    <div>
        <h3 class="text-lg font-semibold mb-3">Favicon</h3>

        @if ($currentFavicon)
            <div class="mb-4">
                <img src="{{ url('storage/' . $currentFavicon) }}" alt="Current Favicon"
                    class="max-w-[32px] border p-1">
                <p class="text-sm text-gray-600 mt-2">Current: {{ $currentFavicon }}</p>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <input type="file" wire:model="favicon" accept="image/*"
                class="block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded file:border-0
                file:text-sm file:font-semibold
                file:bg-indigo-50 file:text-indigo-700
                hover:file:bg-indigo-100">

            @if ($favicon)
                <button wire:click="uploadFavicon"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Upload Favicon
                </button>
            @endif
        </div>

        @error('favicon')
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror

        @if ($favicon)
            <div class="mt-2 text-sm text-gray-600">
                Preview: {{ $favicon->getClientOriginalName() }}
            </div>
        @endif
    </div>

    <div class="mt-6 pt-6 border-t">
        <a href="{{ route('filament.admin.pages.manage-general-settings') }}"
            class="text-indigo-600 hover:text-indigo-800">
            ← Back to System Settings
        </a>
    </div>
</div>
