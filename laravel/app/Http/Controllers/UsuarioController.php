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
        return view('usuario.index');
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
            return redirect()->route('filme.index');
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
            'senha' => 'required|string|min:6',
        ]);

        $usuario = Usuario::create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha' => Hash::make($dados['senha']),
        ]);

        Auth::login($usuario);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
