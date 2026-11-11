<!DOCTYPE html>
<html lang="id">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="description" content="BlogVerse Pro — ruang untuk gagasan, cerita, dan pengetahuan dari komunitas penulis.">
 <meta name="theme-color" content="#f8f8f5">
 <title inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
 <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link rel="icon" type="image/svg+xml" href="/favicon.svg"><link rel="icon" href="/favicon.ico" sizes="any">
 <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" media="print" onload="this.media='all'">
 <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap"></noscript>
 @viteReactRefresh
 @vite(['resources/js/app.jsx'])
 @inertiaHead
</head><body>@inertia</body></html>
