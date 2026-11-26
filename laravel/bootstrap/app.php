<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // quem já está logado e abre o login ou o cadastro vai para a visão geral
        $middleware->redirectUsersTo('/visao-geral');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
