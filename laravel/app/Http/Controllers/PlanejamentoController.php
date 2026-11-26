<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PlanejamentoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        $metas = $usuario->metas()->orderBy('id')->get();
        if ($request->input('ordem') == 'prioridade') {
            $ordem = ['alta' => 1, 'media' => 2, 'sonho' => 3];
            $metas = $metas->sortBy(fn ($meta) => $ordem[$meta->prioridade] ?? 4);
        }

        // o ritmo atual é o que sobrou neste mês, repetido até dezembro
        $hoje = now();
        $doMes = $usuario->movimentacoes()->whereYear('data', $hoje->year)->whereMonth('data', $hoje->month)->get();
        $balanco = $doMes->where('tipo', 'entrada')->sum('valor') - $doMes->where('tipo', 'saida')->sum('valor');
        $projecao = $balanco * (13 - $hoje->month);

        return view('planejamento.index', [
            'metas' => $metas,
            'emAndamento' => $metas->filter(fn ($meta) => $meta->falta() > 0)->count(),
            'projecao' => $projecao,
        ]);
    }

    public function criar()
    {
        return view('planejamento.form', ['meta' => new Meta(['prioridade' => 'alta'])]);
    }

    public function salvar(Request $request)
    {
        Auth::user()->metas()->create($this->validar($request));

        return redirect()->route('planejamento')->with('status', 'Meta criada.');
    }

    public function editar(Meta $meta)
    {
        if ($meta->usuario_id != Auth::id()) {
            abort(404);
        }

        return view('planejamento.form', ['meta' => $meta]);
    }

    public function atualizar(Request $request, Meta $meta)
    {
        if ($meta->usuario_id != Auth::id()) {
            abort(404);
        }

        $meta->update($this->validar($request));

        return redirect()->route('planejamento')->with('status', 'Meta atualizada.');
    }

    public function excluir(Meta $meta)
    {
        if ($meta->usuario_id != Auth::id()) {
            abort(404);
        }

        $meta->delete();

        return redirect()->route('planejamento')->with('status', 'Meta excluída.');
    }

    private function validar(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
            'prioridade' => 'required|in:alta,media,sonho',
            'valor_alvo' => 'required|numeric|min:1|max:99999999',
            'valor_guardado' => 'nullable|numeric|min:0|max:99999999',
            'prazo' => 'nullable|date_format:d/m/Y|after_or_equal:today|before_or_equal:31/12/2100',
        ], [
            'nome.required' => 'Dê um nome para a meta.',
            'nome.max' => 'O nome pode ter no máximo 255 caracteres.',
            'descricao.max' => 'A descrição pode ter no máximo 255 caracteres.',
            'prioridade.required' => 'Escolha a prioridade.',
            'prioridade.in' => 'Escolha uma prioridade da lista.',
            'valor_alvo.required' => 'Informe quanto você quer juntar.',
            'valor_alvo.numeric' => 'Informe um valor válido.',
            'valor_alvo.min' => 'O valor da meta precisa ser de pelo menos R$ 1.',
            'valor_alvo.max' => 'O valor é alto demais.',
            'valor_guardado.numeric' => 'Informe um valor válido.',
            'valor_guardado.min' => 'O valor guardado não pode ser negativo.',
            'valor_guardado.max' => 'O valor é alto demais.',
            'prazo.date_format' => 'Informe uma data válida no formato dd/mm/aaaa.',
            'prazo.after_or_equal' => 'A previsão não pode ser uma data que já passou.',
            'prazo.before_or_equal' => 'A previsão precisa ser até 2100.',
        ]);

        $dados['valor_guardado'] = $dados['valor_guardado'] ?? 0;

        // o campo vem como dd/mm/aaaa e o banco guarda aaaa-mm-dd
        if (! empty($dados['prazo'])) {
            $dados['prazo'] = Carbon::createFromFormat('d/m/Y', $dados['prazo'])->format('Y-m-d');
        }

        return $dados;
    }
}
