<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Sistem Keuangan</title>
    <style>
        :root {
            --bg: #f8fbf8;
            --card: #ffffff;
            --line: #d8e6d8;
            --text: #163322;
            --muted: #5f7b6a;
            --green: #198754;
            --green-strong: #12653f;
            --shadow: 0 18px 40px rgba(25, 135, 84, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Helvetica, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(25, 135, 84, 0.16), transparent 40%),
                radial-gradient(circle at bottom left, rgba(25, 135, 84, 0.1), transparent 45%),
                var(--bg);
        }

        .login-card {
            width: min(100%, 430px);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 26px;
            box-shadow: var(--shadow);
        }

        h1 {
            margin: 0;
            font-size: 1.7rem;
        }

        p {
            margin: 8px 0 0;
            color: var(--muted);
        }

        form {
            display: grid;
            gap: 14px;
            margin-top: 20px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        label {
            color: var(--muted);
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            font: inherit;
        }

        input:focus {
            outline: 2px solid rgba(25, 135, 84, 0.18);
            border-color: var(--green);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .remember input {
            width: auto;
            margin: 0;
        }

        button {
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font: inherit;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--green), var(--green-strong));
        }

        button:hover {
            filter: brightness(0.95);
        }

        .alert {
            margin-top: 14px;
            border-radius: 12px;
            padding: 11px 12px;
            background: #ecf8f0;
            border: 1px solid #b7e2c8;
            color: #236540;
        }

        .errors {
            margin-top: 14px;
            border-radius: 12px;
            padding: 11px 12px;
            background: #fff3f3;
            border: 1px solid #f2c7c7;
            color: #a33e3e;
        }

        .errors ul {
            margin: 0;
            padding-left: 18px;
        }

        .hint {
            margin-top: 16px;
            font-size: 0.88rem;
            color: var(--muted);
        }

        @media (max-width: 520px) {
            .login-card {
                padding: 20px;
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>
    <section class="login-card">
        <h1>Login Admin</h1>
        <p>Akses admin.</p>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST">
            @csrf

            <div class="field">
                <label for="email">Email Admin</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <label class="remember">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Ingat saya
            </label>

            <button type="submit">Masuk</button>
        </form>

        <div class="hint">Default: admin@man2.test</div>
    </section>
</body>
</html>
