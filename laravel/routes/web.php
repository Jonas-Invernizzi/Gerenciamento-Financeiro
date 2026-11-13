om<?php


use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UsuarioController::class, 'logout'])->name('usuario.logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [UsuarioController::class, 'index'])->name('login');
    Route::get('/register', [UsuarioController::class, 'create'])->name('register');
    Route::post('/login', [UsuarioController::class, 'login'])->name('usuario.login');
    Route::post('/register', [UsuarioController::class, 'register'])->name('usuario.register');
});

// Route::fallback(function () {
//     if (Auth::check()) {
//         return redirect()->route('.index');
//     } else {
//         return redirect()->route('login');
//     }
// });
