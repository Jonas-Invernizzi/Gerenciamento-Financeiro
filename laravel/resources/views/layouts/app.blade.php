@php
    $usuario = auth()->user();
    $hoje = \Illuminate\Support\Str::ucfirst(now('America/Sao_Paulo')->locale('pt_BR')->translatedFormat('l, j \d\e F'));
    $menu = [
        ['rota' => 'visao-geral', 'nome' => 'Visão geral', 'curto' => 'Início', 'icone' => 'inicio'],
        ['rota' => 'movimentacoes', 'nome' => 'Movimentações', 'curto' => 'Movimentos', 'icone' => 'movimentos'],
        ['rota' => 'planejamento', 'nome' => 'Planejamento', 'curto' => 'Planejar', 'icone' => 'planejar'],
        ['rota' => 'perfil', 'nome' => 'Meu perfil', 'curto' => 'Perfil', 'icone' => 'perfil'],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') | Norte</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    @vite('resources/css/painel.css')
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <a href="{{ route('principal') }}" class="logo logo--light" aria-label="Norte">
            <span class="logo__mark"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 24V8l16 16V8"/></svg></span>
            <span class="logo__name">norte</span>
        </a>

        <nav class="sidebar__nav" aria-label="Menu">
            @foreach ($menu as $item)
                <a href="{{ $item['rota'] ? route($item['rota']) : '#' }}" @class(['ativo' => $item['rota'] && request()->routeIs($item['rota'], $item['rota'] . '.*')])
                    @if ($item['rota'] && request()->routeIs($item['rota'], $item['rota'] . '.*')) aria-current="page" @endif>
                    @include('layouts.icone', ['nome' => $item['icone']])
                    {{ $item['nome'] }}
                </a>
            @endforeach
        </nav>

        <div class="sidebar__ajuda">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 1.7 4.8L18 10l-4.3 2.2L12 17l-1.7-4.8L6 10l4.3-2.2Z"/></svg>
            <strong>Precisa de ajuda?</strong>
            <span>A Norte está com você.</span>
            <a href="#">Falar com suporte</a>
        </div>

        <form action="{{ route('usuario.logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar__sair">Sair da conta</button>
        </form>
    </aside>

    <div class="app__main">
        <header class="topbar">
            <div>
                <small>{{ $hoje }}</small>
                <p>Olá, {{ $usuario->primeiroNome() }}</p>
            </div>
            <div class="topbar__acoes">
                <button type="button" class="icon-btn" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                </button>
                <button type="button" class="icon-btn icon-btn--aviso" aria-label="Notificações">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>
                    <i></i>
                </button>
                <a href="{{ route('perfil') }}" class="avatar-btn">
                    <span>{{ $usuario->iniciais() }}</span>
                    <div>
                        <strong>{{ $usuario->nome }}</strong>
                        <small>Conta pessoal</small>
                    </div>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </a>
            </div>
        </header>

        <main class="conteudo">
            <div class="mobile-topo">
                <a href="{{ route('principal') }}" class="logo" aria-label="Norte">
                    <span class="logo__mark"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 24V8l16 16V8"/></svg></span>
                    <span class="logo__name">norte</span>
                </a>
                <button type="button" class="icon-btn icon-btn--aviso" aria-label="Notificações">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>
                    <i></i>
                </button>
            </div>
            <div class="mobile-saudacao">
                <small>{{ $hoje }}</small>
                <p>Olá, {{ $usuario->primeiroNome() }}</p>
            </div>

            @yield('conteudo')
        </main>
    </div>

    <nav class="bottom-nav" aria-label="Menu">
        @foreach ($menu as $item)
            <a href="{{ $item['rota'] ? route($item['rota']) : '#' }}" @class(['ativo' => $item['rota'] && request()->routeIs($item['rota'], $item['rota'] . '.*')])>
                @include('layouts.icone', ['nome' => $item['icone']])
                <span>{{ $item['curto'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
@stack('scripts')
</body>
</html>
