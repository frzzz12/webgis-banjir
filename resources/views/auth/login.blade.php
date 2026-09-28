<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Login Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Dekoratif background */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(26,115,232,0.10) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 80%, rgba(13,148,136,0.08) 0%, transparent 60%);
            pointer-events: none; z-index: 0;
        }
        /* Gelombang bawah */
        body::after {
            content: '';
            position: fixed; bottom: -60px; left: -5%;
            width: 110%; height: 220px;
            background: linear-gradient(180deg, transparent 0%, rgba(26,115,232,0.05) 100%);
            border-radius: 50% 50% 0 0 / 60px 60px 0 0;
            pointer-events: none; z-index: 0;
        }

        .login-wrapper {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
            padding: 0 16px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 4px 6px rgba(26,115,232,0.04), 0 20px 60px rgba(26,115,232,0.12);
            overflow: hidden;
        }

        /* Header */
        .login-card-header {
            background: linear-gradient(135deg, #0d47a1 0%, #1a73e8 60%, #0d9488 100%);
            padding: 32px 36px 28px;
            text-align: center;
            position: relative; overflow: hidden;
        }
        .login-card-header::before {
            content: ''; position: absolute;
            top: -40px; right: -40px;
            width: 140px; height: 140px; border-radius: 50%;
            background: rgba(255,255,255,0.05); pointer-events: none;
        }
        .login-card-header::after {
            content: ''; position: absolute;
            bottom: -30px; left: -20px;
            width: 100px; height: 100px; border-radius: 50%;
            background: rgba(13,148,136,0.15); pointer-events: none;
        }

        .brand-icon-wrap {
            display: flex; justify-content: center; margin-bottom: 14px;
        }
        .brand-icon-box {
            width: 62px; height: 62px; border-radius: 18px;
            background: rgba(255,255,255,0.13);
            border: 1.5px solid rgba(255,255,255,0.20);
            display: flex; align-items: center; justify-content: center;
            position: relative; z-index: 1;
        }
        .login-brand-title {
            font-size: 22px; font-weight: 800; color: #fff;
            letter-spacing: -0.02em; margin: 0; line-height: 1.2;
            position: relative; z-index: 1;
        }
        .login-brand-sub {
            font-size: 10.5px; color: rgba(255,255,255,0.55);
            letter-spacing: 0.12em; text-transform: uppercase;
            font-weight: 600; margin-top: 3px;
            position: relative; z-index: 1;
        }

        /* Body */
        .login-card-body { padding: 32px 36px 36px; }
        .login-heading {
            font-size: 18px; font-weight: 700; color: #1a1c1f;
            margin: 0 0 4px;
        }
        .login-subheading { font-size: 13px; color: #737780; margin: 0 0 28px; }

        .field-label {
            display: block; font-size: 11px; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            color: #43474f; margin-bottom: 6px;
        }
        .input-wrapper { position: relative; margin-bottom: 18px; }
        .input-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            width: 17px; height: 17px; color: #b0b3bb; pointer-events: none;
        }
        .field-input {
            display: block; width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid #e2e2e7; border-radius: 12px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            color: #1a1c1f; background: #f9f9fe;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        .field-input:focus {
            border-color: #1a73e8; background: #fff;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.10);
        }
        .field-input::placeholder { color: #b0b3bb; }

        .field-error { font-size: 11px; color: #ba1a1a; margin-top: 5px; display: block; }

        .row-remember {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 24px;
        }
        .remember-label {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; color: #43474f;
            cursor: pointer; user-select: none;
        }
        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px;
            border-radius: 4px; accent-color: #1a73e8; cursor: pointer;
        }

        .btn-login {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 13px 0;
            background: linear-gradient(135deg, #0d47a1, #1a73e8);
            color: #fff; font-family: 'Inter', sans-serif;
            font-size: 13px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            border: none; border-radius: 14px; cursor: pointer;
            transition: all .2s;
            box-shadow: 0 4px 16px rgba(26,115,232,0.25);
        }
        .btn-login:hover {
            box-shadow: 0 6px 22px rgba(26,115,232,0.35);
            transform: translateY(-1px);
            filter: brightness(1.05);
        }
        .btn-login:active { transform: scale(0.98); }

        .back-home { text-align: center; margin-top: 20px; }
        .back-home a {
            font-size: 12px; color: #737780; text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
            transition: color .2s; font-weight: 600;
        }
        .back-home a:hover { color: #1a73e8; }

        .alert-error {
            background: #fff1f2; border: 1px solid #fecdd3;
            border-radius: 10px; padding: 10px 14px;
            font-size: 13px; color: #be123c; font-weight: 500;
            margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- CARD -->
        <div class="login-card">

            <!-- Header -->
            <div class="login-card-header">
                <div class="brand-icon-wrap">
                    <div class="brand-icon-box">
                        <svg width="34" height="34" viewBox="0 0 44 44" fill="none">
                            <!-- Ikon tetesan air / banjir -->
                            <path d="M22 6 C22 6 10 18 10 27 C10 33.6 15.4 39 22 39 C28.6 39 34 33.6 34 27 C34 18 22 6 22 6Z" fill="white" opacity="0.9"/>
                            <path d="M16 29 Q18 26 22 28 Q26 30 28 27" stroke="rgba(13,148,136,0.8)" stroke-width="2" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="login-brand-title">WebGIS Banjir</p>
                <p class="login-brand-sub">Kecamatan Kendari Barat</p>
            </div>

            <!-- Body -->
            <div class="login-card-body">
                <h2 class="login-heading">Selamat datang</h2>
                <p class="login-subheading">Masuk untuk mengakses panel admin</p>

                @if ($errors->any())
                    <div class="alert-error">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="field-label">Alamat Email</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.75L2.25 6.75"/>
                            </svg>
                            <input id="email" class="field-input" type="email" name="email"
                                value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="admin@webgis-banjir.test"/>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="field-label">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                            <input id="password" class="field-input" type="password" name="password"
                                required autocomplete="current-password" placeholder="••••••••"/>
                        </div>
                    </div>

                    <!-- Remember -->
                    <div class="row-remember">
                        <label class="remember-label" for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-login">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H5.25"/>
                        </svg>
                        Masuk ke Dashboard
                    </button>
                </form>

                <div class="back-home">
                    <a href="{{ url('/') }}">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                        </svg>
                        Kembali ke Peta
                    </a>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
