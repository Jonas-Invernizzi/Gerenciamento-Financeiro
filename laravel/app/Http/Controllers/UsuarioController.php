<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuario.login');
    }
    public function create()
    {
        return view('usuario.create');
    }
    public function login(Request $request)
    {
        $dados = $request->validate([
            'email' => 'required|string|email|max:255',
            'senha' => 'required|string|min:6',
        ]);

        $credenciais = [
            'email' => $dados['email'],
            'password' => $dados['senha'],
        ];

        if(Auth::attempt($credenciais)){
            $request->session()->regenerate();
            return redirect()->intended(route('visao-geral'));
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }
    public function register(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'senha' => 'required|string|min:8',
            'termos' => 'accepted',
        ], [
            'nome.required' => 'Informe seu nome.',
            'nome.max' => 'O nome pode ter no máximo 255 caracteres.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail pode ter no máximo 255 caracteres.',
            'email.unique' => 'Já existe uma conta com este e-mail.',
            'senha.required' => 'Crie uma senha.',
            'senha.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'termos.accepted' => 'Para criar a conta, aceite os Termos de Uso e a Política de Privacidade.',
        ]);

        Usuario::create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => Hash::make($dados['senha']),
        ]);

        return redirect()->route('login')->with('status', 'Conta criada! Agora é só entrar com seu e-mail e senha.');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
