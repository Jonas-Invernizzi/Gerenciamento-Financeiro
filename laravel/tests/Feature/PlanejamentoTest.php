<?php

namespace Tests\Feature;

use App\Models\Meta;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlanejamentoTest extends TestCase
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

    private function meta(Usuario $usuario, array $mudar = []): Meta
    {
        return $usuario->metas()->create(array_merge([
            'nome' => 'Reserva de emergência',
            'descricao' => 'Proteção para 6 meses de despesas',
            'prioridade' => 'alta',
            'valor_alvo' => 12000,
            'valor_guardado' => 8600,
            'prazo' => '2027-09-01',
        ], $mudar));
    }

    private function dados(array $mudar = []): array
    {
        return array_merge([
            'nome' => 'Viagem para Portugal',
            'descricao' => 'Passagens, hospedagem e passeios',
            'prioridade' => 'sonho',
            'valor_alvo' => '9500',
            'valor_guardado' => '4250',
            'prazo' => '01/05/2028',
        ], $mudar);
    }

    public function test_sem_login_vai_para_o_login(): void
    {
        $this->get('/planejamento')->assertRedirect(route('login'));
        $this->get('/planejamento/criar')->assertRedirect(route('login'));
        $this->post('/planejamento', $this->dados())->assertRedirect(route('login'));

        $this->assertSame(0, Meta::count());
    }

    public function test_tela_abre_com_usuario_logado(): void
    {
        $this->actingAs($this->usuario())
            ->get('/planejamento')
            ->assertOk()
            ->assertSee('Seus próximos passos')
            ->assertSee('Minhas metas')
            ->assertSee('0 objetivos em andamento')
            ->assertSee('Adicionar uma meta');
    }

    public function test_menu_e_atalho_da_visao_geral_levam_para_o_planejamento(): void
    {
        $this->actingAs($this->usuario())
            ->get('/visao-geral')
            ->assertSee('href="' . route('planejamento') . '"', false);
    }

    public function test_tela_de_nova_meta_abre(): void
    {
        $this->actingAs($this->usuario())
            ->get('/planejamento/criar')
            ->assertOk()
            ->assertSee('Dados da meta')
            ->assertSee('Criar meta');
    }

    public function test_cria_meta(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->post('/planejamento', $this->dados())
            ->assertRedirect(route('planejamento'))
            ->assertSessionHas('status', 'Meta criada.');

        $meta = Meta::first();
        $this->assertSame($usuario->id, $meta->usuario_id);
        $this->assertSame('Viagem para Portugal', $meta->nome);
        $this->assertSame('sonho', $meta->prioridade);
        $this->assertSame(9500.0, $meta->valor_alvo);
        $this->assertSame(4250.0, $meta->valor_guardado);
    }

    public function test_valor_guardado_vazio_vira_zero(): void
    {
        $this->actingAs($this->usuario())
            ->post('/planejamento', $this->dados(['valor_guardado' => '', 'prazo' => '', 'descricao' => '']));

        $meta = Meta::first();
        $this->assertSame(0.0, $meta->valor_guardado);
        $this->assertNull($meta->prazo);
    }

    public function test_meta_aparece_com_o_progresso(): void
    {
        $usuario = $this->usuario();
        $this->meta($usuario);

        $this->actingAs($usuario)
            ->get('/planejamento')
            ->assertSee('Reserva de emergência')
            ->assertSee('Prioridade alta')
            ->assertSeeInOrder(['R$ 8.600', 'de R$ 12.000', '72%'])
            ->assertSee('Faltam R$ 3.400')
            ->assertSee('Previsão: set. 2027')
            ->assertSee('1 objetivo em andamento');
    }

    public function test_campos_obrigatorios(): void
    {
        $this->actingAs($this->usuario())
            ->post('/planejamento', [])
            ->assertSessionHasErrors([
                'nome' => 'Dê um nome para a meta.',
                'prioridade' => 'Escolha a prioridade.',
                'valor_alvo' => 'Informe quanto você quer juntar.',
            ]);

        $this->assertSame(0, Meta::count());
    }

    public function test_valores_invalidos_dao_erro(): void
    {
        $this->actingAs($this->usuario())
            ->post('/planejamento', $this->dados([
                'valor_alvo' => '0',
                'valor_guardado' => '-10',
                'prioridade' => 'urgente',
            ]))
            ->assertSessionHasErrors([
                'valor_alvo' => 'O valor da meta precisa ser de pelo menos R$ 1.',
                'valor_guardado' => 'O valor guardado não pode ser negativo.',
                'prioridade' => 'Escolha uma prioridade da lista.',
            ]);

        $this->assertSame(0, Meta::count());
    }

    public function test_previsao_no_formato_dia_mes_ano(): void
    {
        $this->actingAs($this->usuario())
            ->post('/planejamento', $this->dados(['prazo' => '26/12/2030']))
            ->assertSessionHasNoErrors();

        $this->assertSame('2030-12-26', Meta::first()->prazo->format('Y-m-d'));
    }

    public function test_previsao_invalida_da_erro(): void
    {
        $usuario = $this->usuario();

        foreach (['2030-12-26', '31/02/2030', '26/12/10500'] as $prazo) {
            $this->actingAs($usuario)
                ->post('/planejamento', $this->dados(['prazo' => $prazo]))
                ->assertSessionHasErrors(['prazo' => 'Informe uma data válida no formato dd/mm/aaaa.']);
        }

        $this->actingAs($usuario)
            ->post('/planejamento', $this->dados(['prazo' => now()->subDay()->format('d/m/Y')]))
            ->assertSessionHasErrors(['prazo' => 'A previsão não pode ser uma data que já passou.']);

        $this->actingAs($usuario)
            ->post('/planejamento', $this->dados(['prazo' => '01/01/2101']))
            ->assertSessionHasErrors(['prazo' => 'A previsão precisa ser até 2100.']);

        $this->assertSame(0, Meta::count());
    }

    public function test_atualiza_o_valor_guardado(): void
    {
        $usuario = $this->usuario();
        $meta = $this->meta($usuario);

        $this->actingAs($usuario)
            ->get('/planejamento/' . $meta->id . '/editar')
            ->assertOk()
            ->assertSee('Editar meta')
            ->assertSee('value="01/09/2027"', false)
            ->assertSee('Reserva de emergência');

        $this->actingAs($usuario)
            ->put('/planejamento/' . $meta->id, $this->dados([
                'nome' => 'Reserva de emergência',
                'prioridade' => 'alta',
                'valor_alvo' => '12000',
                'valor_guardado' => '12000',
            ]))
            ->assertRedirect(route('planejamento'))
            ->assertSessionHas('status', 'Meta atualizada.');

        $this->assertSame(12000.0, $meta->refresh()->valor_guardado);

        $this->actingAs($usuario)
            ->get('/planejamento')
            ->assertSee('100%')
            ->assertSee('Meta concluída!')
            ->assertSee('0 objetivos em andamento');
    }

    public function test_exclui_meta(): void
    {
        $usuario = $this->usuario();
        $meta = $this->meta($usuario);

        $this->actingAs($usuario)
            ->delete('/planejamento/' . $meta->id)
            ->assertRedirect(route('planejamento'))
            ->assertSessionHas('status', 'Meta excluída.');

        $this->assertSame(0, Meta::count());
    }

    public function test_nao_ve_metas_de_outro_usuario(): void
    {
        $this->meta($this->usuario('outro@email.com'), ['nome' => 'Meta do outro']);

        $this->actingAs($this->usuario())
            ->get('/planejamento')
            ->assertDontSee('Meta do outro')
            ->assertSee('0 objetivos em andamento');
    }

    public function test_nao_mexe_na_meta_de_outro_usuario(): void
    {
        $meta = $this->meta($this->usuario('outro@email.com'));
        $usuario = $this->usuario();

        $this->actingAs($usuario)->get('/planejamento/' . $meta->id . '/editar')->assertNotFound();
        $this->actingAs($usuario)->put('/planejamento/' . $meta->id, $this->dados())->assertNotFound();
        $this->actingAs($usuario)->delete('/planejamento/' . $meta->id)->assertNotFound();

        $this->assertSame('Reserva de emergência', $meta->refresh()->nome);
    }

    public function test_ordena_por_prioridade(): void
    {
        $usuario = $this->usuario();
        $this->meta($usuario, ['nome' => 'Viagem dos sonhos', 'prioridade' => 'sonho']);
        $this->meta($usuario, ['nome' => 'Trocar o celular', 'prioridade' => 'media']);
        $this->meta($usuario, ['nome' => 'Pagar o cartão', 'prioridade' => 'alta']);

        $this->actingAs($usuario)
            ->get('/planejamento')
            ->assertSeeInOrder(['Viagem dos sonhos', 'Trocar o celular', 'Pagar o cartão']);

        $this->actingAs($usuario)
            ->get('/planejamento?ordem=prioridade')
            ->assertSeeInOrder(['Pagar o cartão', 'Trocar o celular', 'Viagem dos sonhos']);
    }

    public function test_projecao_usa_o_que_sobrou_no_mes(): void
    {
        $usuario = $this->usuario();
        $usuario->movimentacoes()->create(['descricao' => 'Salário', 'tipo' => 'entrada', 'categoria' => 'Salário', 'valor' => 3000, 'data' => now()]);
        $usuario->movimentacoes()->create(['descricao' => 'Aluguel', 'tipo' => 'saida', 'categoria' => 'Casa', 'valor' => 1000, 'data' => now()]);

        $esperado = number_format(2000 * (13 - now()->month), 0, ',', '.');

        $this->actingAs($usuario)
            ->get('/planejamento')
            ->assertSee('você pode guardar <strong>R$ ' . $esperado . '</strong> até o fim do ano', false);
    }

    public function test_projecao_sem_sobra_mostra_aviso(): void
    {
        $this->actingAs($this->usuario())
            ->get('/planejamento')
            ->assertSee('Neste mês ainda não sobrou dinheiro.')
            ->assertDontSee('Mantendo seu ritmo atual');
    }
}
