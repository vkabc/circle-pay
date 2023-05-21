<?php

use App\Http\Controllers\ProfileController;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/payment', function () {
    return Inertia::render('Payment');
})->name('payment');

Route::get('/qrcode/{id}', function (int $id) {


    $url = \route('payment', ['id' => $id]);
    $svg = (new Writer(
        new ImageRenderer(
            new RendererStyle(256),
            new SvgImageBackEnd()
        )
    ))->writeString($url);

    $svg = trim(substr($svg, strpos($svg, "\n") + 1));


    return Inertia::render('QrCode', [
        'id' => $id,
        'svg' => $svg,
        'url' => $url,
    ]);
})->name('qrcode');

Route::get('/payment/{id}', function (int $id) {
    return Inertia::render('Payment', [
        'id' => $id,
    ]);
})->name('payment');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
