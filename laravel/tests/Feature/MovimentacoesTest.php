<?php

namespace Tests\Feature;

use App\Models\Movimentacao;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MovimentacoesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function usuario(string $email = 'maria@email.com'): Usuario
    {
        return Usuario::create([
            'nome' => 'Maria Souza',
            'email' => $email,
            'senha' => Hash::make('segredo123'),
        ]);
    }

    private function movimentacao(Usuario $usuario, array $mudar = []): Movimentacao
    {
        return $usuario->movimentacoes()->create(array_merge([
            'descricao' => 'Mercado do mês',
            'tipo' => 'saida',
            'categoria' => 'Mercado',
            'valor' => 186.42,
            'data' => now(),
        ], $mudar));
    }

    private function dados(array $mudar = []): array
    {
        return array_merge([
            'descricao' => 'Salário',
            'tipo' => 'entrada',
            'categoria' => 'Salário',
            'valor' => '4850.00',
            'data' => now()->format('d/m/Y'),
        ], $mudar);
    }

    public function test_sem_login_vai_para_o_login(): void
    {
        $this->get('/movimentacoes')->assertRedirect(route('login'));
        $this->get('/movimentacoes/criar')->assertRedirect(route('login'));
        $this->post('/movimentacoes', $this->dados())->assertRedirect(route('login'));

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_tela_abre_com_usuario_logado(): void
    {
        $this->actingAs($this->usuario())
            ->get('/movimentacoes')
            ->assertOk()
            ->assertSee('Seu histórico')
            ->assertSee('Nova movimentação')
            ->assertSee('Nenhuma movimentação encontrada.');
    }

    public function test_menu_leva_para_movimentacoes(): void
    {
        $this->actingAs($this->usuario())
            ->get('/perfil')
            ->assertSee('href="' . route('movimentacoes') . '"', false);
    }

    public function test_tela_de_nova_movimentacao_abre(): void
    {
        $this->actingAs($this->usuario())
            ->get('/movimentacoes/criar')
            ->assertOk()
            ->assertSee('Dados da movimentação')
            ->assertSee('Adicionar movimentação');
    }

    public function test_adiciona_movimentacao(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->post('/movimentacoes', $this->dados())
            ->assertRedirect(route('movimentacoes'))
            ->assertSessionHas('status', 'Movimentação adicionada.');

        $movimentacao = Movimentacao::first();
        $this->assertSame($usuario->id, $movimentacao->usuario_id);
        $this->assertSame('Salário', $movimentacao->descricao);
        $this->assertSame('entrada', $movimentacao->tipo);
        $this->assertSame(4850.0, $movimentacao->valor);
    }

    public function test_movimentacao_aparece_na_lista(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario);

        $this->actingAs($usuario)
            ->get('/movimentacoes')
            ->assertSee('Mercado do mês')
            ->assertSee('Mercado • Hoje')
            ->assertSee('− R$ 186,42')
            ->assertSee('Conta principal');
    }

    public function test_campos_obrigatorios(): void
    {
        $this->actingAs($this->usuario())
            ->post('/movimentacoes', [])
            ->assertSessionHasErrors([
                'descricao' => 'Informe uma descrição.',
                'tipo' => 'Escolha se é uma entrada ou uma saída.',
                'categoria' => 'Escolha uma categoria.',
                'valor' => 'Informe o valor.',
                'data' => 'Informe a data.',
            ]);

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_valor_precisa_ser_maior_que_zero(): void
    {
        $this->actingAs($this->usuario())
            ->post('/movimentacoes', $this->dados(['valor' => '0']))
            ->assertSessionHasErrors(['valor' => 'O valor precisa ser maior que zero.']);

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_valor_precisa_ser_numero(): void
    {
        $this->actingAs($this->usuario())
            ->post('/movimentacoes', $this->dados(['valor' => 'abc']))
            ->assertSessionHasErrors(['valor' => 'Informe um valor válido.']);
    }

    public function test_data_no_formato_dia_mes_ano(): void
    {
        $this->actingAs($this->usuario())
            ->post('/movimentacoes', $this->dados(['data' => '26/12/2026']))
            ->assertSessionHasNoErrors();

        $this->assertSame('2026-12-26', Movimentacao::first()->data->format('Y-m-d'));
    }

    public function test_data_invalida_da_erro(): void
    {
        $usuario = $this->usuario();

        foreach (['2026-12-26', '31/02/2026', '26/12/10500', 'abc'] as $data) {
            $this->actingAs($usuario)
                ->post('/movimentacoes', $this->dados(['data' => $data]))
                ->assertSessionHasErrors(['data' => 'Informe uma data válida no formato dd/mm/aaaa.']);
        }

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_ano_precisa_estar_entre_2000_e_2100(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->post('/movimentacoes', $this->dados(['data' => '31/12/1999']))
            ->assertSessionHasErrors(['data' => 'A data precisa ser de 2000 em diante.']);

        $this->actingAs($usuario)
            ->post('/movimentacoes', $this->dados(['data' => '01/01/2101']))
            ->assertSessionHasErrors(['data' => 'A data precisa ser até 2100.']);

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_tipo_e_categoria_precisam_ser_da_lista(): void
    {
        $this->actingAs($this->usuario())
            ->post('/movimentacoes', $this->dados(['tipo' => 'outro', 'categoria' => 'Qualquer coisa']))
            ->assertSessionHasErrors(['tipo', 'categoria' => 'Escolha uma categoria da lista.']);

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_edita_movimentacao(): void
    {
        $usuario = $this->usuario();
        $movimentacao = $this->movimentacao($usuario);

        $this->actingAs($usuario)
            ->get('/movimentacoes/' . $movimentacao->id . '/editar')
            ->assertOk()
            ->assertSee('Editar movimentação')
            ->assertSee('Mercado do mês')
            ->assertSee('value="' . now()->format('d/m/Y') . '"', false);

        $this->actingAs($usuario)
            ->put('/movimentacoes/' . $movimentacao->id, $this->dados([
                'descricao' => 'Feira',
                'tipo' => 'saida',
                'categoria' => 'Mercado',
                'valor' => '50.5',
            ]))
            ->assertRedirect(route('movimentacoes'))
            ->assertSessionHas('status', 'Movimentação atualizada.');

        $movimentacao->refresh();
        $this->assertSame('Feira', $movimentacao->descricao);
        $this->assertSame(50.5, $movimentacao->valor);
    }

    public function test_exclui_movimentacao(): void
    {
        $usuario = $this->usuario();
        $movimentacao = $this->movimentacao($usuario);

        $this->actingAs($usuario)
            ->delete('/movimentacoes/' . $movimentacao->id)
            ->assertRedirect(route('movimentacoes'))
            ->assertSessionHas('status', 'Movimentação excluída.');

        $this->assertSame(0, Movimentacao::count());
    }

    public function test_nao_ve_movimentacoes_de_outro_usuario(): void
    {
        $outro = $this->usuario('outro@email.com');
        $this->movimentacao($outro, ['descricao' => 'Compra do outro']);

        $this->actingAs($this->usuario())
            ->get('/movimentacoes')
            ->assertDontSee('Compra do outro')
            ->assertSee('Nenhuma movimentação encontrada.');
    }

    public function test_nao_mexe_na_movimentacao_de_outro_usuario(): void
    {
        $outro = $this->usuario('outro@email.com');
        $movimentacao = $this->movimentacao($outro);
        $usuario = $this->usuario();

        $this->actingAs($usuario)->get('/movimentacoes/' . $movimentacao->id . '/editar')->assertNotFound();
        $this->actingAs($usuario)->put('/movimentacoes/' . $movimentacao->id, $this->dados())->assertNotFound();
        $this->actingAs($usuario)->delete('/movimentacoes/' . $movimentacao->id)->assertNotFound();

        $this->assertSame('Mercado do mês', $movimentacao->refresh()->descricao);
    }

    public function test_filtra_entradas_e_saidas(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['descricao' => 'Conta de luz']);
        $this->movimentacao($usuario, ['descricao' => 'Pagamento do freela', 'tipo' => 'entrada', 'categoria' => 'Transferência']);

        $this->actingAs($usuario)
            ->get('/movimentacoes?tipo=entrada')
            ->assertSee('Pagamento do freela')
            ->assertDontSee('Conta de luz');

        $this->actingAs($usuario)
            ->get('/movimentacoes?tipo=saida')
            ->assertSee('Conta de luz')
            ->assertDontSee('Pagamento do freela');
    }

    public function test_busca_pela_descricao(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['descricao' => 'Netflix']);
        $this->movimentacao($usuario, ['descricao' => 'Uber']);

        $this->actingAs($usuario)
            ->get('/movimentacoes?busca=net')
            ->assertSee('Netflix')
            ->assertDontSee('Uber');
    }

    public function test_resumo_conta_so_o_mes_atual(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 1100]);
        $this->movimentacao($usuario, ['valor' => 300]);
        $this->movimentacao($usuario, ['valor' => 999, 'data' => now()->subMonths(3)]);
        $this->movimentacao($usuario, ['tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 1000, 'data' => now()->startOfMonth()->subMonth()]);

        $this->actingAs($usuario)
            ->get('/movimentacoes')
            ->assertSeeInOrder(['Entradas em', 'R$ 1.100,00', '+10% no período'])
            ->assertSeeInOrder(['Saídas em', 'R$ 300,00', '1 lançamento'])
            ->assertSeeInOrder(['Balanço do mês', 'R$ 800,00', 'Positivo']);
    }
}
