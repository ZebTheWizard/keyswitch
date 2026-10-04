<?php

use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/{directory}/{photo}.{format}', function (string $directory, string $photo, string $format) {
    return Image::fromStorage("{$directory}/{$photo}.webp", 'public')
        ->toFormat($format)
        ->quality(80);
})->where('format', 'avif|jpg|jpeg|webp|png')
    ->where('directory', 'covers|products');
