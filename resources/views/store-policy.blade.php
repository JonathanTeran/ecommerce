<x-layouts.app :title="$policy['title'] ?? __('Policy')">
    <div class="bg-slate-50 dark:bg-zinc-950 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-10 space-y-3">
                <h1 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 dark:text-white">
                    {{ $policy['title'] ?? '' }}
                </h1>
            </header>

            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-8">
                <div class="prose dark:prose-invert max-w-none prose-headings:font-heading prose-a:text-primary-600 hover:prose-a:text-primary-500">
                    {!! $policy['content'] ?? '' !!}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
