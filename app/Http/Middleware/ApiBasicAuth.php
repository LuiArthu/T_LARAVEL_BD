<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autenticação HTTP Basic (e-mail + senha) sem sessão, para a API.
 *
 * Usa a mesma tabela users do app web, então os papéis (admin/instrutor/aluno)
 * e as policies valem igualmente. Use SEMPRE sobre HTTPS fora de ambiente local.
 * Para token por usuário o caminho natural é o Laravel Sanctum (não instalado).
 *
 * As credenciais são reavaliadas a CADA requisição: de propósito, o middleware
 * não confia em nenhum usuário que já esteja no guard (evita vazar a identidade
 * de uma requisição para a seguinte em workers de vida longa). Por isso o
 * acesso à API é só por Basic; testes devem enviar o cabeçalho Authorization.
 */
class ApiBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->getUser();
        $senha = $request->getPassword();

        if (filled($email) && filled($senha)) {
            $provider = Auth::guard()->getProvider();
            $usuario = $provider->retrieveByCredentials(['email' => $email]);

            if ($usuario && $provider->validateCredentials($usuario, ['password' => $senha])) {
                Auth::setUser($usuario);

                return $next($request);
            }
        }

        return response()->json(['message' => 'Não autenticado.'], 401, [
            'WWW-Authenticate' => 'Basic realm="Academia Lutas API"',
        ]);
    }
}
