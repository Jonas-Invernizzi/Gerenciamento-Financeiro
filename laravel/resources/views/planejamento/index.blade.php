@extends('layouts.app')

@section('titulo', 'Planejamento')

@section('conteudo')
    <div class="modulo-topo">
        <div>
            <span class="eyebrow">Seus próximos passos</span>
            <h1>Planejamento</h1>
            <small>Metas possíveis para transformar planos em conquistas.</small>
        </div>
        <a href="{{ route('planejamento.criar') }}" class="modulo-topo__botao">
            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Criar nova meta
        </a>
    </div>

    @if (session('status'))
        <div class="alerta" role="status">{{ session('status') }}</div>
    @endif

    <section class="projecao">
        <div>
            <span>
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 1.7 4.8L18 10l-4.3 2.2L12 17l-1.7-4.8L6 10l4.3-2.2Z"/></svg>
                Projeção Norte
            </span>
            @if ($projecao > 0)
                <p>Mantendo seu ritmo atual, você pode guardar <strong>R$ {{ number_format($projecao, 0, ',', '.') }}</strong> até o fim do ano.</p>
            @else
                <p>Neste mês ainda não sobrou dinheiro. Registre suas entradas e saídas para ver quanto dá para guardar até o fim do ano.</p>
            @endif
            <a href="{{ route('movimentacoes') }}">
                Ver minha projeção
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </a>
        </div>
        <div class="projecao__barras" aria-hidden="true">
            <i></i><i></i><i></i><i></i><i></i><i></i><i></i>
        </div>
    </section>

    <div class="metas-topo">
        <div>
            <h2>Minhas metas</h2>
            <small>{{ $emAndamento }} {{ $emAndamento == 1 ? 'objetivo' : 'objetivos' }} em andamento</small>
        </div>
        <a href="{{ route('planejamento', ['ordem' => 'prioridade']) }}">
            Ordenar por prioridade
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </a>
    </div>

    <div class="metas">
        @foreach ($metas as $meta)
            <article class="meta">
                <div class="meta__topo">
                    @if ($meta->prioridade == 'sonho')
                        <span class="meta__icone meta__icone--sonho">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg>
                        </span>
                    @else
                        <span class="meta__icone">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                        </span>
                    @endif
                    <span class="meta__selo">{{ $meta->selo() }}</span>
                    <a href="{{ route('planejamento.editar', $meta) }}" aria-label="Opções para {{ $meta->nome }}">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
                    </a>
                </div>

                <h3>{{ $meta->nome }}</h3>
                <small>{{ $meta->descricao }}</small>

                <div class="meta__valor">
                    <strong>R$ {{ number_format($meta->valor_guardado, 0, ',', '.') }}</strong>
                    <span>de R$ {{ number_format($meta->valor_alvo, 0, ',', '.') }}</span>
                    <b>{{ $meta->porcentagem() }}%</b>
                </div>
                <div @class(['meta__barra', 'meta__barra--sonho' => $meta->prioridade == 'sonho'])>
                    <i style="width: {{ $meta->porcentagem() }}%"></i>
                </div>

                <footer>
                    @if ($meta->falta() > 0)
                        <span>Faltam R$ {{ number_format($meta->falta(), 0, ',', '.') }}</span>
                    @else
                        <span>Meta concluída!</span>
                    @endif

                    @if ($meta->prazo)
                        <span>Previsão: {{ $meta->prazo->locale('pt_BR')->translatedFormat('M. Y') }}</span>
                    @else
                        <span>Sem previsão</span>
                    @endif
                </footer>
            </article>
        @endforeach

        <a href="{{ route('planejamento.criar') }}" class="meta-nova">
            <span>
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            </span>
            <strong>Adicionar uma meta</strong>
            <small>Comece a planejar seu próximo objetivo</small>
        </a>
    </div>
@endsection
