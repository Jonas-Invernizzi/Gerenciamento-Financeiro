@extends('layouts.app')

@section('titulo', $meta->exists ? 'Editar meta' : 'Nova meta')

@section('conteudo')
    <div class="modulo-topo">
        <div>
            <span class="eyebrow">Seus próximos passos</span>
            <h1>{{ $meta->exists ? 'Editar meta' : 'Nova meta' }}</h1>
            <small>Defina quanto quer juntar e acompanhe o que já guardou.</small>
        </div>
        <a href="{{ route('planejamento') }}" class="voltar">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/><path d="M9 12h10"/></svg>
            Voltar
        </a>
    </div>

    <div class="config">
        <h2>Dados da meta</h2>

        <form action="{{ $meta->exists ? route('planejamento.atualizar', $meta) : route('planejamento.salvar') }}" method="POST" novalidate>
            @csrf
            @if ($meta->exists)
                @method('PUT')
            @endif
            <div class="config__campos">
                <label>
                    <span>Nome da meta</span>
                    <input type="text" name="nome" value="{{ old('nome', $meta->nome) }}" placeholder="Ex.: Reserva de emergência" required
                        @error('nome') aria-invalid="true" @enderror>
                    @error('nome') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Descrição</span>
                    <input type="text" name="descricao" value="{{ old('descricao', $meta->descricao) }}" placeholder="Ex.: Proteção para 6 meses de despesas"
                        @error('descricao') aria-invalid="true" @enderror>
                    @error('descricao') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Quanto quero juntar (R$)</span>
                    <input type="number" name="valor_alvo" value="{{ old('valor_alvo', $meta->valor_alvo) }}" step="0.01" min="1" placeholder="0,00" inputmode="decimal" required
                        @error('valor_alvo') aria-invalid="true" @enderror>
                    @error('valor_alvo') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Quanto já guardei (R$)</span>
                    <input type="number" name="valor_guardado" value="{{ old('valor_guardado', $meta->valor_guardado) }}" step="0.01" min="0" placeholder="0,00" inputmode="decimal"
                        @error('valor_guardado') aria-invalid="true" @enderror>
                    @error('valor_guardado') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Prioridade</span>
                    <select name="prioridade" @error('prioridade') aria-invalid="true" @enderror>
                        <option value="alta" @selected(old('prioridade', $meta->prioridade) == 'alta')>Prioridade alta</option>
                        <option value="media" @selected(old('prioridade', $meta->prioridade) == 'media')>Prioridade média</option>
                        <option value="sonho" @selected(old('prioridade', $meta->prioridade) == 'sonho')>Sonho</option>
                    </select>
                    @error('prioridade') <em class="erro">{{ $message }}</em> @enderror
                </label>
                <label>
                    <span>Previsão</span>
                    <input type="text" name="prazo" value="{{ old('prazo', $meta->prazo?->format('d/m/Y')) }}" class="campo-data" placeholder="dd/mm/aaaa" maxlength="10" inputmode="numeric"
                        @error('prazo') aria-invalid="true" @enderror>
                    @error('prazo') <em class="erro">{{ $message }}</em> @enderror
                </label>
            </div>
            <button type="submit" class="config__salvar">{{ $meta->exists ? 'Salvar alterações' : 'Criar meta' }}</button>
        </form>

        @if ($meta->exists)
            <div class="config__divisoria"></div>

            <form action="{{ route('planejamento.excluir', $meta) }}" method="POST" onsubmit="return confirm('Excluir esta meta?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="botao-excluir">Excluir meta</button>
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
