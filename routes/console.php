<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('blog:about', function () { $this->info('BlogVerse Pro'); })->purpose('Display BlogVerse information');
