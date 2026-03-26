<div class="flex items-center space-x-4">
    <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" 
       class="text-sm font-medium {{ app()->getLocale() === 'es' ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
       🇪🇸 ES
    </a>
    <span class="text-gray-300">|</span>
    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" 
       class="text-sm font-medium {{ app()->getLocale() === 'en' ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
       🇺🇸 EN
    </a>
</div>
