@extends('layouts.app')

@section('titulo', 'Visão geral')

@section('conteudo')
    <section class="saldo">
        <div class="saldo__topo">
            <div>
                <span>Saldo disponível</span>
                <strong>R$ {{ number_format($saldo, 2, ',', '.') }}</strong>
            </div>
            <button type="button" id="ocultarSaldo" aria-label="Ocultar saldo">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
            </button>
        </div>
        <div class="saldo__numeros">
            <div>
                <span class="saldo__seta saldo__seta--entrada">↗</span>
                <span>Entradas <strong>R$ {{ number_format($entradas, 2, ',', '.') }}</strong></span>
            </div>
            <div>
                <span class="saldo__seta saldo__seta--saida">↘</span>
                <span>Saídas <strong>R$ {{ number_format($saidas, 2, ',', '.') }}</strong></span>
            </div>
        </div>
        <div class="saldo__rodape">
            <span>Resumo de {{ $mes }}</span>
            <span>{{ $mesConcluido }}% do mês concluído</span>
        </div>
    </section>

    <nav class="atalhos" aria-label="Ações rápidas">
        <a href="{{ route('movimentacoes.criar', ['tipo' => 'entrada']) }}">
            <span><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg></span>
            Adicionar
        </a>
        <a href="{{ route('movimentacoes.criar', ['tipo' => 'saida']) }}">
            <span><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3v18l3-2 3 2 3-2 3 2V3l-3 2-3-2-3 2Z"/><path d="M9 10h6M9 14h5"/></svg></span>
            Pagar
        </a>
        <a href="{{ route('planejamento') }}">
            <span><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg></span>
            Planejar
        </a>
        <a href="{{ route('movimentacoes') }}">
            <span><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg></span>
            Mais
        </a>
    </nav>

    <section class="insight">
        <span class="insight__icone">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 1.7 4.8L18 10l-4.3 2.2L12 17l-1.7-4.8L6 10l4.3-2.2Z"/></svg>
        </span>
        <div>
            <span>Insight da Norte</span>
            <strong>{{ $insight }}</strong>
            <a href="{{ route('movimentacoes') }}">
                Ver análise
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
    </section>

    <div class="visao-grid">
        <section class="recentes">
            <div class="secao-topo">
                <div>
                    <span>Movimentações</span>
                    <small>Últimas atividades</small>
                </div>
                <a href="{{ route('movimentacoes') }}">Ver todas</a>
            </div>

            @forelse ($recentes as $movimentacao)
                <a href="{{ route('movimentacoes.editar', $movimentacao) }}" class="recente">
                    <span class="letra letra--{{ $movimentacao->cor() }}">{{ $movimentacao->letra() }}</span>
                    <span class="recente__texto">
                        <strong>{{ $movimentacao->descricao }}</strong>
                        <small>{{ $movimentacao->categoria }} • {{ $movimentacao->dataCurta() }}</small>
                    </span>
                    <strong @class(['positivo' => $movimentacao->tipo == 'entrada'])>{{ $movimentacao->valorFormatado() }}</strong>
                </a>
            @empty
                <p class="vazio">Nenhuma movimentação ainda.</p>
            @endforelse
        </section>

        <section class="orcamento">
            <div class="secao-topo">
                <div>
                    <span>Orçamento do mês</span>
                    <small>{{ ucfirst($mes) }} de {{ now()->year }}</small>
                </div>
                <a href="{{ route('movimentacoes', ['tipo' => 'saida']) }}" aria-label="Ver as saídas do mês">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>

            <div class="orcamento__grafico" style="background: conic-gradient(#16a291 0 {{ min($usado, 100) }}%, #e8efed {{ min($usado, 100) }}% 100%)">
                <span>
                    <b>{{ $usado }}%</b>
                    <small>utilizado</small>
                </span>
            </div>

            <div class="orcamento__numeros">
                <div>
                    <span>Gasto</span>
                    <strong>R$ {{ number_format($saidas, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Limite</span>
                    <strong>R$ {{ number_format($entradas, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if ($saidas <= $entradas)
                <div class="orcamento__aviso">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    Você está dentro do planejado
                </div>
            @else
                <div class="orcamento__aviso orcamento__aviso--fora">Você gastou mais do que entrou</div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('ocultarSaldo').addEventListener('click', () => {
            document.querySelector('.saldo').classList.toggle('saldo--oculto');
        });
    </script>
@endpush
