@props(['data' => []])

<section class="py-12 bg-white dark:bg-zinc-950">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose dark:prose-invert max-w-none prose-headings:font-heading prose-a:text-primary-600 hover:prose-a:text-primary-500">
            {!! $data['content'] ?? '' !!}
        </div>
    </div>
</section>
