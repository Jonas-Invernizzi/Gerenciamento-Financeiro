<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function usuario(array $mudar = []): Usuario
    {
        return Usuario::create(array_merge([
            'nome' => 'Maria Souza',
            'email' => 'maria@email.com',
            'senha' => Hash::make('segredo123'),
        ], $mudar));
    }

    public function test_sem_login_vai_para_o_login(): void
    {
        $this->get('/perfil')->assertRedirect(route('login'));
    }

    public function test_perfil_mostra_os_dados(): void
    {
        $this->actingAs($this->usuario())
            ->get('/perfil')
            ->assertOk()
            ->assertSee('Meu perfil')
            ->assertSee('Olá, Maria')
            ->assertSee('maria@email.com')
            ->assertSee('MS');
    }

    public function test_atualiza_os_dados(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->put('/perfil', [
                'nome' => 'Maria da Silva',
                'email' => 'maria.silva@email.com',
                'telefone' => '(54) 99999-1234',
                'cpf' => '123.456.789-09',
            ])
            ->assertRedirect(route('perfil'))
            ->assertSessionHas('status');

        $usuario->refresh();
        $this->assertSame('Maria da Silva', $usuario->nome);
        $this->assertSame('maria.silva@email.com', $usuario->email);
        $this->assertSame('(54) 99999-1234', $usuario->telefone);
        $this->assertSame('12345678909', $usuario->cpf);
    }

    public function test_cpf_nao_muda_depois_de_salvo(): void
    {
        $usuario = $this->usuario(['cpf' => '12345678909']);

        $this->actingAs($usuario)->put('/perfil', [
            'nome' => 'Maria Souza',
            'email' => 'maria@email.com',
            'cpf' => '999.999.999-99',
        ]);

        $this->assertSame('12345678909', $usuario->refresh()->cpf);
    }

    public function test_nao_deixa_usar_email_de_outra_conta(): void
    {
        $this->usuario(['email' => 'outra@email.com']);
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->put('/perfil', ['nome' => 'Maria', 'email' => 'outra@email.com'])
            ->assertSessionHasErrors(['email' => 'Já existe uma conta com este e-mail.']);
    }

    public function test_salva_as_preferencias(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->put('/perfil/preferencias', ['resumo_semanal' => '1'])
            ->assertRedirect(route('perfil'));

        $usuario->refresh();
        $this->assertFalse($usuario->notificacoes_gastos);
        $this->assertTrue($usuario->resumo_semanal);
    }

    public function test_troca_a_senha(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->put('/perfil/senha', [
                'senha_atual' => 'segredo123',
                'nova_senha' => 'novasenha456',
                'nova_senha_confirmation' => 'novasenha456',
            ])
            ->assertRedirect(route('perfil'));

        $this->assertTrue(Hash::check('novasenha456', $usuario->refresh()->senha));
    }

    public function test_senha_atual_errada_nao_troca(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->put('/perfil/senha', [
                'senha_atual' => 'errada000',
                'nova_senha' => 'novasenha456',
                'nova_senha_confirmation' => 'novasenha456',
            ])
            ->assertSessionHasErrorsIn('senha', ['senha_atual']);

        $this->assertTrue(Hash::check('segredo123', $usuario->refresh()->senha));
    }
}
