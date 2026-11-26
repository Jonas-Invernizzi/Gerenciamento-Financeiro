<?php


use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PlanejamentoController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VisaoGeralController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PrincipalController::class, 'index'])->name('principal');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UsuarioController::class, 'logout'])->name('usuario.logout');

    Route::get('/visao-geral', [VisaoGeralController::class, 'index'])->name('visao-geral');

    Route::get('/movimentacoes', [MovimentacaoController::class, 'index'])->name('movimentacoes');
    Route::get('/movimentacoes/criar', [MovimentacaoController::class, 'criar'])->name('movimentacoes.criar');
    Route::post('/movimentacoes', [MovimentacaoController::class, 'salvar'])->name('movimentacoes.salvar');
    Route::get('/movimentacoes/{movimentacao}/editar', [MovimentacaoController::class, 'editar'])->name('movimentacoes.editar');
    Route::put('/movimentacoes/{movimentacao}', [MovimentacaoController::class, 'atualizar'])->name('movimentacoes.atualizar');
    Route::delete('/movimentacoes/{movimentacao}', [MovimentacaoController::class, 'excluir'])->name('movimentacoes.excluir');

    Route::get('/planejamento', [PlanejamentoController::class, 'index'])->name('planejamento');
    Route::get('/planejamento/criar', [PlanejamentoController::class, 'criar'])->name('planejamento.criar');
    Route::post('/planejamento', [PlanejamentoController::class, 'salvar'])->name('planejamento.salvar');
    Route::get('/planejamento/{meta}/editar', [PlanejamentoController::class, 'editar'])->name('planejamento.editar');
    Route::put('/planejamento/{meta}', [PlanejamentoController::class, 'atualizar'])->name('planejamento.atualizar');
    Route::delete('/planejamento/{meta}', [PlanejamentoController::class, 'excluir'])->name('planejamento.excluir');

    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'atualizar'])->name('perfil.atualizar');
    Route::put('/perfil/preferencias', [PerfilController::class, 'preferencias'])->name('perfil.preferencias');
    Route::put('/perfil/senha', [PerfilController::class, 'senha'])->name('perfil.senha');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [UsuarioController::class, 'index'])->name('login');
    Route::get('/register', [UsuarioController::class, 'create'])->name('register');
    Route::post('/login', [UsuarioController::class, 'login'])->name('usuario.login');
    Route::post('/register', [UsuarioController::class, 'register'])->name('usuario.register');
});

Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('visao-geral');
    } else {
        return redirect()->route('login');
    }
});
