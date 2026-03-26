<div class="mt-16 border-t border-gray-200 dark:border-zinc-800 pt-10">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ __('Opiniones de Clientes') }}</h2>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        {{-- Summary Column --}}
        <div class="lg:col-span-4 space-y-8">
            <div class="bg-gray-50 dark:bg-zinc-900 p-6 rounded-2xl">
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-5xl font-bold text-gray-900 dark:text-white">{{ $averageRating ?? '0.0' }}</span>
                    <span class="text-lg text-gray-500 mb-1">/ 5</span>
                </div>
                <div class="flex items-center gap-1 mb-4">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                    <span class="text-sm text-gray-500 ml-2">({{ $reviewsCount }} {{ __('reviews') }})</span>
                </div>

                <div class="space-y-2">
                    @foreach ($breakdown as $stars => $data)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-3 text-gray-600 dark:text-gray-400">{{ $stars }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-400 rounded-full"
                                    style="width: {{ $data['percentage'] }}%">
                                </div>
                            </div>
                            <span class="w-8 text-right text-gray-400">{{ $data['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Review Form --}}
            <div
                class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 p-6 rounded-2xl shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('Escribir una Reseña') }}
                </h3>
                @auth
                    @if (session()->has('message'))
                        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-900/30 dark:text-green-400"
                            role="alert">
                            {{ session('message') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="submitReview">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Your rating') }}</label>
                            <div class="flex gap-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" wire:click="$set('rating', {{ $i }})"
                                        class="focus:outline-none transition-colors duration-200">
                                        <svg class="w-8 h-8 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600 hover:text-yellow-200' }}"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            @error('rating')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Title') }}</label>
                            <input type="text" id="title" wire:model="title"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="{{ __('Short summary of your experience') }}">
                            @error('title')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="comment"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Comment') }}</label>
                            <textarea id="comment" wire:model="comment" rows="4"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="{{ __('What did you like most? What could be improved?') }}"></textarea>
                            @error('comment')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        @error('check')
                            <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400"
                                role="alert">
                                {{ $message }}
                            </div>
                        @enderror

                        <button type="submit"
                            class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition">
                            {{ __('Enviar Reseña') }}
                        </button>
                    </form>
                @else
                    <div class="text-center py-6">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ __('Debes iniciar sesión publicar reseñas.') }}
                        </p>
                        <a href="{{ class_exists(\Filament\Facades\Filament::class) ? filament()->getPanel('buyer')->getLoginUrl() : url('/buyer/login') }}"
                            class="inline-block px-6 py-2 border border-gray-300 dark:border-zinc-700 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            {{ __('Iniciar Sesión') }}
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Reviews List --}}
        <div class="lg:col-span-8">
            @if ($reviews->count() > 0)
                <div class="space-y-6">
                    @foreach ($reviews as $review)
                        <div class="border-b border-gray-100 dark:border-zinc-800 pb-6 last:border-0">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-700 dark:text-indigo-400 font-bold">
                                        {{ substr($review->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $review->user->name }}
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span>{{ $review->created_at->diffForHumans() }}</span>
                                            @if ($review->is_verified_purchase)
                                                <span
                                                    class="text-green-600 dark:text-green-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    {{ __('Verified Purchase') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200 dark:text-zinc-700' }}"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ $review->title }}</h4>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ $review->comment }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 dark:bg-zinc-900 rounded-2xl">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">{{ __('Sin reseñas todavía') }}
                    </h3>
                    <p class="text-gray-500">{{ __('Sé el primero en compartir tu experiencia con este producto.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
