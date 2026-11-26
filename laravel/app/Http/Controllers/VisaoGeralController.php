<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class VisaoGeralController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $hoje = now();

        $todas = $usuario->movimentacoes()->get();
        $saldo = $todas->where('tipo', 'entrada')->sum('valor') - $todas->where('tipo', 'saida')->sum('valor');

        $doMes = $usuario->movimentacoes()->whereYear('data', $hoje->year)->whereMonth('data', $hoje->month)->get();
        $entradas = $doMes->where('tipo', 'entrada')->sum('valor');
        $saidas = $doMes->where('tipo', 'saida')->sum('valor');

        // o limite do orçamento é o que entrou no mês
        $usado = 0;
        if ($entradas > 0) {
            $usado = round($saidas / $entradas * 100);
        } elseif ($saidas > 0) {
            $usado = 100;
        }

        $mesPassado = $hoje->copy()->startOfMonth()->subMonth();
        $saidasMesPassado = $usuario->movimentacoes()->where('tipo', 'saida')
            ->whereYear('data', $mesPassado->year)->whereMonth('data', $mesPassado->month)->sum('valor');

        $insight = 'Adicione suas entradas e saídas para receber dicas sobre os seus gastos.';
        if ($saidasMesPassado > 0) {
            $diferenca = round(($saidas - $saidasMesPassado) / $saidasMesPassado * 100);
            if ($diferenca < 0) {
                $insight = 'Você gastou ' . abs($diferenca) . '% menos este mês do que no mês passado.';
            } elseif ($diferenca > 0) {
                $insight = 'Você gastou ' . $diferenca . '% a mais este mês do que no mês passado.';
            } else {
                $insight = 'Você gastou o mesmo que no mês passado.';
            }
        }

        return view('visao-geral.index', [
            'saldo' => $saldo,
            'entradas' => $entradas,
            'saidas' => $saidas,
            'usado' => $usado,
            'insight' => $insight,
            'recentes' => $usuario->movimentacoes()->orderByDesc('data')->orderByDesc('id')->take(4)->get(),
            'mes' => $hoje->locale('pt_BR')->translatedFormat('F'),
            'mesConcluido' => round($hoje->day / $hoje->daysInMonth * 100),
        ]);
    }
}
