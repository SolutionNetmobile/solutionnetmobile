<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/prestations/{prestation:slug}', [PrestationController::class, 'show'])->name('prestations.show');

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
