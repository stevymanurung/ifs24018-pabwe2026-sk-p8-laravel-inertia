<!DOCTYPE html>
<html lang="id">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="description" content="BlogVerse Pro — ruang untuk gagasan, cerita, dan pengetahuan dari komunitas penulis.">
 <meta name="theme-color" content="#f8f8f5">
 <title inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
 <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
 @viteReactRefresh
 @vite(['resources/css/app.css', 'resources/js/app.jsx'])
 @inertiaHead
</head><body>@inertia</body></html>
