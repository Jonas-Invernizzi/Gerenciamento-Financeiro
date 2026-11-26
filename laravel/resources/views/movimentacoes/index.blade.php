@extends('layouts.app')

@section('titulo', 'Movimentações')

@section('conteudo')
    <div class="modulo-topo">
        <div>
            <span class="eyebrow">Seu histórico</span>
            <h1>Movimentações</h1>
            <small>Acompanhe tudo que entra e sai da sua conta.</small>
        </div>
        <a href="{{ route('movimentacoes.criar') }}" class="modulo-topo__botao">
            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Nova movimentação
        </a>
    </div>

    @if (session('status'))
        <div class="alerta" role="status">{{ session('status') }}</div>
    @endif

    @php $balanco = $entradas - $saidas; @endphp
    <div class="resumo">
        <article>
            <span class="resumo__icone resumo__icone--entrada">↗</span>
            <div>
                <small>Entradas em {{ $mes }}</small>
                <strong>R$ {{ number_format($entradas, 2, ',', '.') }}</strong>
                @if ($variacao === null)
                    <em class="neutro">Sem dados do mês passado</em>
                @else
                    <em @class(['negativo' => $variacao < 0])>{{ $variacao > 0 ? '+' : '' }}{{ $variacao }}% no período</em>
                @endif
            </div>
        </article>
        <article>
            <span class="resumo__icone resumo__icone--saida">↘</span>
            <div>
                <small>Saídas em {{ $mes }}</small>
                <strong>R$ {{ number_format($saidas, 2, ',', '.') }}</strong>
                <em class="neutro">{{ $lancamentos }} {{ $lancamentos == 1 ? 'lançamento' : 'lançamentos' }}</em>
            </div>
        </article>
        <article>
            <span class="resumo__icone resumo__icone--balanco">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h14a2 2 0 0 1 2 2v11H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h13"/><path d="M16 11h6v5h-6a2.5 2.5 0 0 1 0-5Z"/></svg>
            </span>
            <div>
                <small>Balanço do mês</small>
                <strong>R$ {{ number_format($balanco, 2, ',', '.') }}</strong>
                <em @class(['negativo' => $balanco < 0])>{{ $balanco < 0 ? 'Negativo' : 'Positivo' }}</em>
            </div>
        </article>
    </div>

    <div class="lista">
        <div class="filtros">
            <form action="{{ route('movimentacoes') }}" method="GET" class="filtros__busca">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                @if ($tipo)
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                @endif
                <input type="search" name="busca" value="{{ $busca }}" placeholder="Buscar movimentação" aria-label="Buscar movimentação">
            </form>
            <div class="filtros__tipos">
                <a href="{{ route('movimentacoes', ['busca' => $busca]) }}" @class(['ativo' => $tipo != 'entrada' && $tipo != 'saida'])>Todas</a>
                <a href="{{ route('movimentacoes', ['tipo' => 'entrada', 'busca' => $busca]) }}" @class(['ativo' => $tipo == 'entrada'])>Entradas</a>
                <a href="{{ route('movimentacoes', ['tipo' => 'saida', 'busca' => $busca]) }}" @class(['ativo' => $tipo == 'saida'])>Saídas</a>
            </div>
        </div>

        @forelse ($dias as $lista)
            @php $data = $lista->first()->data->locale('pt_BR'); @endphp
            <div class="dia">
                @if ($data->isToday())
                    Hoje,
                @elseif ($data->isYesterday())
                    Ontem,
                @endif
                {{ $data->translatedFormat('j \d\e F') }}
            </div>

            @foreach ($lista as $movimentacao)
                <div class="movimento">
                    <span class="letra letra--{{ $movimentacao->cor() }}">{{ $movimentacao->letra() }}</span>
                    <div>
                        <strong>{{ $movimentacao->descricao }}</strong>
                        <small>{{ $movimentacao->categoria }} • {{ $movimentacao->dataCurta() }}</small>
                    </div>
                    <span>Conta principal</span>
                    <b @class(['positivo' => $movimentacao->tipo == 'entrada'])>{{ $movimentacao->valorFormatado() }}</b>
                    <a href="{{ route('movimentacoes.editar', $movimentacao) }}" aria-label="Opções para {{ $movimentacao->descricao }}">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
                    </a>
                </div>
            @endforeach
        @empty
            <p class="vazio">Nenhuma movimentação encontrada.</p>
        @endforelse
    </div>
@endsection
