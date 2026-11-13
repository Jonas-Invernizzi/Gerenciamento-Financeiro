<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | Norte</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href=" @vite('resources/css/login.css')">
</head>
<body>
<main class="login">
    {@if ($errors->any())}
        <div class="alert alert-danger">
            <ul>
                {@foreach ($errors->all() as $error)}
                    <li>{{ $error }}</li>
                {@endforeach}
            </ul>
        </div>
    {

    @endif}

        <section class="brand">
        <span class="brand__ring brand__ring--top" aria-hidden="true"></span>
        <span class="brand__ring brand__ring--bottom" aria-hidden="true"></span>

        <a href="#" class="logo" aria-label="Norte">
            <span class="logo__mark">N</span>
            <span class="logo__name">norte</span>
        </a>

        <div class="brand__content">
            <p class="eyebrow eyebrow--light">Seu dinheiro, na direção certa</p>
            <h1 class="brand__title">Clareza hoje.<br>Liberdade amanhã.</h1>
            <p class="brand__text">
                Organize sua vida financeira sem planilhas complicadas e tome decisões melhores todos os dias.
            </p>

            <div class="stat">
                <span class="stat__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M9 20V4M14 20v-8M19 20v-5M3 20h18"/></svg>
                </span>
                <div>
                    <strong class="stat__value">+12% este mês</strong>
                    <span class="stat__label">Economia média dos nossos usuários</span>
                </div>
            </div>
        </div>

        <p class="brand__footer">Simples. Seguro. Feito para você.</p>
    </section>

        <section class="form-side">
        <div class="form-box">
            <p class="eyebrow">Boas-vindas de volta</p>
            <h2 class="form-box__title">Entre na sua conta</h2>
            <p class="form-box__subtitle">Continue construindo uma vida financeira mais leve.</p>

            <form action="{{ route('usuario.login') }}" method="POST" class="form">
                @csrf
                <div class="field">
                    <label for="email" class="field__label">E-mail</label>
                    <div class="input">
                        <svg class="input__icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <input type="email" id="email" name="email" placeholder="voce@email.com" autocomplete="email" required>
                    </div>
                </div>

                <div class="field">
                    <label for="password" class="field__label">Senha</label>
                    <div class="input">
                        <svg class="input__icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        <input type="password" id="password" name="password" placeholder="Sua senha" autocomplete="current-password" required>
                        <button type="button" class="input__toggle" id="togglePassword" aria-label="Mostrar senha">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form__row">
                    <label class="check">
                        <input type="checkbox" name="remember">
                        <span>Lembrar de mim</span>
                    </label>
                    <a href="#" class="link">Esqueci minha senha</a>
                </div>

                <button type="submit" class="btn btn--primary">
                    Entrar
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>

                <div class="divider"><span>ou</span></div>

                <a href="#" class="btn btn--outline">
                    <svg viewBox="0 0 48 48" width="18" height="18" aria-hidden="true"><path fill="#4285F4" d="M45.1 24.5c0-1.6-.1-3.1-.4-4.5H24v8.6h11.9c-.5 2.8-2.1 5.1-4.5 6.7v5.6h7.3c4.3-3.9 6.4-9.7 6.4-16.4Z"/><path fill="#34A853" d="M24 46c6.1 0 11.2-2 14.9-5.5l-7.3-5.6c-2 1.4-4.6 2.2-7.6 2.2-5.9 0-10.8-4-12.6-9.3H3.9v5.8C7.6 40.9 15.2 46 24 46Z"/><path fill="#FBBC05" d="M11.4 28c-.5-1.4-.7-2.8-.7-4.3s.3-2.9.7-4.3v-5.8H3.9C2.4 16.6 1.5 20.2 1.5 24s.9 7.4 2.4 10.4l7.5-6.4Z"/><path fill="#EA4335" d="M24 10.4c3.3 0 6.3 1.1 8.7 3.4l6.5-6.5C35.2 3.6 30.1 1.5 24 1.5 15.2 1.5 7.6 6.6 3.9 14l7.5 5.8c1.8-5.3 6.7-9.4 12.6-9.4Z"/></svg>
                    Continuar com Google
                </a>
            </form>

            <p class="signup">Ainda não tem uma conta? <a href="#" class="link">Criar conta</a></p>

            <p class="secure">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                Seus dados estão protegidos com criptografia
            </p>
        </div>
    </section>
</main>

<script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    toggle.addEventListener('click', () => {
        const show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
    });
</script>
</body>
</html>
