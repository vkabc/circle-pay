<?php

use App\Http\Controllers\ProfileController;
use App\Models\Record;
use App\Models\Transaction;
use App\Models\User;
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
    $userId = auth()->id();
    $records = Record::where('from', $userId)->orWhere('to', $userId)->latest()->get();
    $names = $records->map(function ($record) {
            $name = User::find($record->to)->name;
        return $name;
    });
    return Inertia::render('Dashboard', [
        'names' => $names,
        'amount' => auth()->user()->balance->amount,
        'id' => $userId,
        'records' => $records,
    ]);
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
    if (Auth::check()) {
        $amount = auth()->user()->balance->amount;
    }
    return Inertia::render('Payment', [
        'id' => $id,
        'amount' => $amount ?? null,
    ]);
})->name('payment');

Route::get('success', function () {
    return Inertia::render('Success');
})->name('success');

Route::post('/payment/{id}', function (int $id) {

    $user = User::findOrFail($id);

    DB::transaction(function () use ($user, $id){
        $user->balance->amount += request()->amount;
        $user->balance->save();
        Record::create([
            'to' => $id,
            'amount' => request()->amount,
        ]);
    });

    return true;
})->name('payment');

Route::post('/internal-payment/{id}', function (int $id) {

    $user = User::findOrFail($id);

    DB::transaction(function () use ($user, $id){
        $balance = auth()->user()->balance;
        if($balance->amount < request()->amount){
            throw new \Exception('Not enough money');
        }
        $user->balance->amount += request()->amount;

        $user->balance->save();
        Record::create([
            'from' => auth()->id(),
            'to' => $id,
            'amount' => request()->amount,
        ]);
    });

    return true;
})->name('internal-payment');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
