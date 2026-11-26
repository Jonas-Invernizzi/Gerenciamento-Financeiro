@extends('layouts.app')

@section('titulo', $movimentacao->exists ? 'Editar movimentação' : 'Nova movimentação')

@section('conteudo')
    <div class="modulo-topo">
        <div>
            <span class="eyebrow">Seu histórico</span>
            <h1>{{ $movimentacao->exists ? 'Editar movimentação' : 'Nova movimentação' }}</h1>
            <small>Registre o que entrou ou saiu da sua conta.</small>
        </div>
        <a href="{{ route('movimentacoes') }}" class="voltar">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/><path d="M9 12h10"/></svg>
            Voltar
        </a>
    </div>

    <div class="config">
        <h2>Dados da movimentação</h2>

        <form action="{{ $movimentacao->exists ? route('movimentacoes.atualizar', $movimentacao) : route('movimentacoes.salvar') }}" method="POST" novalidate>
            @csrf
            @if ($movimentacao->exists)
                @method('PUT')
            @endif
            <div class="config__campos">
                <label>
                    <span>Descrição</span>
                    <input type="text" name="descricao" value="{{ old('descricao', $movimentacao->descricao) }}" placeholder="Ex.: Mercado, salário, aluguel" required
                        @error('descricao') aria-invalid="true" @enderror>
                    @error('descricao') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Valor (R$)</span>
                    <input type="number" name="valor" value="{{ old('valor', $movimentacao->valor) }}" step="0.01" min="0.01" placeholder="0,00" inputmode="decimal" required
                        @error('valor') aria-invalid="true" @enderror>
                    @error('valor') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Tipo</span>
                    <select name="tipo" @error('tipo') aria-invalid="true" @enderror>
                        <option value="saida" @selected(old('tipo', $movimentacao->tipo) == 'saida')>Saída</option>
                        <option value="entrada" @selected(old('tipo', $movimentacao->tipo) == 'entrada')>Entrada</option>
                    </select>
                    @error('tipo') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Categoria</span>
                    <select name="categoria" @error('categoria') aria-invalid="true" @enderror>
                        <option value="">Escolha uma categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria }}" @selected(old('categoria', $movimentacao->categoria) == $categoria)>{{ $categoria }}</option>
                        @endforeach
                    </select>
                    @error('categoria') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Data</span>
                    <input type="text" name="data" value="{{ old('data', $movimentacao->data?->format('d/m/Y')) }}" class="campo-data" placeholder="dd/mm/aaaa" maxlength="10" inputmode="numeric" required
                        @error('data') aria-invalid="true" @enderror>
                    @error('data') <em class="erro">{{ $message }}</em> @enderror
                </label>
            </div>
            <button type="submit" class="config__salvar">{{ $movimentacao->exists ? 'Salvar alterações' : 'Adicionar movimentação' }}</button>
        </form>

        @if ($movimentacao->exists)
            <div class="config__divisoria"></div>

            <form action="{{ route('movimentacoes.excluir', $movimentacao) }}" method="POST" onsubmit="return confirm('Excluir esta movimentação?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="botao-excluir">Excluir movimentação</button>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // coloca as barras sozinho: 26122026 vira 26/12/2026
        document.querySelectorAll('.campo-data').forEach((campo) => {
            campo.addEventListener('input', () => {
                let numeros = campo.value.replace(/\D/g, '').slice(0, 8);
                if (numeros.length > 4) {
                    numeros = numeros.slice(0, 2) + '/' + numeros.slice(2, 4) + '/' + numeros.slice(4);
                } else if (numeros.length > 2) {
                    numeros = numeros.slice(0, 2) + '/' + numeros.slice(2);
                }
                campo.value = numeros;
            });
        });
    </script>
@endpush
