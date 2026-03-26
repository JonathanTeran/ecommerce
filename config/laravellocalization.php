<?php

return [

    // Uncomment the languages that your site supports - or add new ones.
    // These are the languages that your site will support.
    // The keys are the 'locales' and the values are the 'properties' of the locale.
    'supportedLocales' => [
        'es' => ['name' => 'Spanish', 'script' => 'Latn', 'native' => 'Español', 'regional' => 'es_ES'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
    ],

    // Negotiate for the user locale using the Accept-Language header if it's not defined in the URL?
    'useAcceptLanguageHeader' => true,

    // If LaravelLocalizationRedirectFilter is active and hideDefaultLocaleInURL
    // is true, the url would not have the default application language
    'hideDefaultLocaleInURL' => false,

    // If user tries to access the root using a non supported locale,
    // they will be redirected to the default locale URL (if useAcceptLanguageHeader is true)
    'localesOrder' => [],

    //  If you want to use custom URL for a specific locale, you can change the key of the locale
    //  and provide the 'url' key for the locale.
    'localesMapping' => [],

    // If you want to use the 'utf8mb4' charset for the database, you can set this to true.
    'utf8' => false,

    // If you want to use the 'laravel-localization' middleware, you can set this to true.
    'routeGroup' => [],
];
