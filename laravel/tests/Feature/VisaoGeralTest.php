<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisaoGeralTest extends TestCase
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

    private function movimentacao(Usuario $usuario, array $mudar = []): void
    {
        $usuario->movimentacoes()->create(array_merge([
            'descricao' => 'Mercado do mês',
            'tipo' => 'saida',
            'categoria' => 'Mercado',
            'valor' => 100,
            'data' => now(),
        ], $mudar));
    }

    public function test_sem_login_vai_para_o_login(): void
    {
        $this->get('/visao-geral')->assertRedirect(route('login'));
    }

    public function test_abre_com_usuario_logado(): void
    {
        $this->actingAs($this->usuario())
            ->get('/visao-geral')
            ->assertOk()
            ->assertSee('Olá, Maria')
            ->assertSee('Saldo disponível');
    }

    public function test_login_leva_para_a_visao_geral(): void
    {
        $this->usuario();

        $this->post('/login', ['email' => 'maria@email.com', 'senha' => 'segredo123'])
            ->assertRedirect(route('visao-geral'));
        $this->assertAuthenticated();
    }

    public function test_rota_que_nao_existe_com_login_volta_para_a_visao_geral(): void
    {
        $this->actingAs($this->usuario())
            ->get('/qualquer-coisa')
            ->assertRedirect(route('visao-geral'));
    }

    public function test_logado_que_abre_login_ou_cadastro_vai_para_a_visao_geral(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)->get('/login')->assertRedirect('/visao-geral');
        $this->actingAs($usuario)->get('/register')->assertRedirect('/visao-geral');
    }

    public function test_menu_leva_para_a_visao_geral(): void
    {
        $this->actingAs($this->usuario())
            ->get('/perfil')
            ->assertSee('href="' . route('visao-geral') . '"', false);
    }

    public function test_sem_movimentacoes_mostra_tudo_zerado(): void
    {
        $this->actingAs($this->usuario())
            ->get('/visao-geral')
            ->assertSeeInOrder(['Saldo disponível', 'R$ 0,00'])
            ->assertSee('Nenhuma movimentação ainda.')
            ->assertSee('Adicione suas entradas e saídas para receber dicas sobre os seus gastos.');
    }

    public function test_calcula_saldo_entradas_e_saidas(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 1000]);
        $this->movimentacao($usuario, ['valor' => 300]);
        $this->movimentacao($usuario, ['tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 500, 'data' => now()->subMonths(2)]);

        $this->actingAs($usuario)
            ->get('/visao-geral')
            ->assertSeeInOrder(['Saldo disponível', 'R$ 1.200,00', 'Entradas', 'R$ 1.000,00', 'Saídas', 'R$ 300,00']);
    }

    public function test_mostra_so_as_quatro_ultimas_movimentacoes(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['descricao' => 'Mais antiga', 'data' => now()->subDays(10)]);
        foreach (['Netflix', 'Uber', 'Farmácia', 'Padaria'] as $descricao) {
            $this->movimentacao($usuario, ['descricao' => $descricao]);
        }

        $this->actingAs($usuario)
            ->get('/visao-geral')
            ->assertSee('Netflix')
            ->assertSee('Padaria')
            ->assertDontSee('Mais antiga');
    }

    public function test_orcamento_usa_as_entradas_do_mes_como_limite(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 1000]);
        $this->movimentacao($usuario, ['valor' => 250]);

        $this->actingAs($usuario)
            ->get('/visao-geral')
            ->assertSeeInOrder(['Orçamento do mês', '25%', 'utilizado', 'Gasto', 'R$ 250', 'Limite', 'R$ 1.000'])
            ->assertSee('Você está dentro do planejado');
    }

    public function test_avisa_quando_gasta_mais_do_que_entrou(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 100]);
        $this->movimentacao($usuario, ['valor' => 150]);

        $this->actingAs($usuario)
            ->get('/visao-geral')
            ->assertSee('150%')
            ->assertSee('Você gastou mais do que entrou')
            ->assertDontSee('Você está dentro do planejado');
    }

    public function test_insight_compara_os_gastos_com_o_mes_passado(): void
    {
        $usuario = $this->usuario();
        $this->movimentacao($usuario, ['valor' => 1000, 'data' => now()->startOfMonth()->subMonth()]);
        $this->movimentacao($usuario, ['valor' => 820]);

        $this->actingAs($usuario)
            ->get('/visao-geral')
            ->assertSee('Você gastou 18% menos este mês do que no mês passado.');
    }

    public function test_nao_mostra_dados_de_outro_usuario(): void
    {
        $outro = $this->usuario('outro@email.com');
        $this->movimentacao($outro, ['descricao' => 'Compra do outro', 'valor' => 5000]);

        $this->actingAs($this->usuario())
            ->get('/visao-geral')
            ->assertDontSee('Compra do outro')
            ->assertDontSee('5.000')
            ->assertSee('Nenhuma movimentação ainda.');
    }

    public function test_atalhos_levam_para_adicionar_e_pagar(): void
    {
        $this->actingAs($this->usuario())
            ->get('/visao-geral')
            ->assertSee('href="' . route('movimentacoes.criar', ['tipo' => 'entrada']) . '"', false)
            ->assertSee('href="' . route('movimentacoes.criar', ['tipo' => 'saida']) . '"', false);
    }
}
