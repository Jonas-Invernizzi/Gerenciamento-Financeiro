<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = ['nome', 'email', 'senha', 'telefone', 'cpf', 'notificacoes_gastos', 'resumo_semanal'];

    protected $hidden = ['senha'];

    protected $authPasswordName = 'senha';

    protected $casts = [
        'notificacoes_gastos' => 'boolean',
        'resumo_semanal' => 'boolean',
    ];

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }

    public function metas()
    {
        return $this->hasMany(Meta::class);
    }

    public function primeiroNome(): string
    {
        return explode(' ', trim($this->nome))[0];
    }

    public function iniciais(): string
    {
        $partes = preg_split('/\s+/', trim($this->nome));
        $iniciais = mb_substr($partes[0], 0, 1);
        if (count($partes) > 1) {
            $iniciais .= mb_substr(end($partes), 0, 1);
        }
        return mb_strtoupper($iniciais);
    }

    public function cpfEscondido(): ?string
    {
        if (! $this->cpf) {
            return null;
        }
        return '•••.•••.•••-' . substr($this->cpf, -2);
    }

}
