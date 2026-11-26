<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    protected $table = 'movimentacoes';

    protected $fillable = ['descricao', 'tipo', 'categoria', 'valor', 'data'];

    protected $casts = [
        'valor' => 'float',
        'data' => 'date',
    ];

    public function letra(): string
    {
        return mb_strtoupper(mb_substr($this->descricao, 0, 1));
    }

    public function cor(): string
    {
        if ($this->tipo == 'entrada') {
            return 'verde';
        }

        $cores = ['Assinaturas' => 'vermelho', 'Transporte' => 'roxo'];

        return $cores[$this->categoria] ?? 'coral';
    }

    public function valorFormatado(): string
    {
        $sinal = $this->tipo == 'entrada' ? '+' : '−';

        return $sinal . ' R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function dataCurta(): string
    {
        if ($this->data->isToday()) {
            return 'Hoje';
        }
        if ($this->data->isYesterday()) {
            return 'Ontem';
        }

        return $this->data->locale('pt_BR')->translatedFormat('j M');
    }
}
