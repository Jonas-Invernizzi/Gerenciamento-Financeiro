<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $table = 'metas';

    protected $fillable = ['nome', 'descricao', 'prioridade', 'valor_alvo', 'valor_guardado', 'prazo'];

    protected $casts = [
        'valor_alvo' => 'float',
        'valor_guardado' => 'float',
        'prazo' => 'date',
    ];

    public function porcentagem(): int
    {
        if ($this->valor_alvo <= 0) {
            return 0;
        }

        return min(100, round($this->valor_guardado / $this->valor_alvo * 100));
    }

    public function falta(): float
    {
        return max(0, $this->valor_alvo - $this->valor_guardado);
    }

    public function selo(): string
    {
        $selos = ['alta' => 'Prioridade alta', 'media' => 'Prioridade média', 'sonho' => 'Sonho'];

        return $selos[$this->prioridade] ?? '';
    }
}
