<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Terjadi kendala') - KONI Klaten</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: #f4f5f7; color: #17191f; }
        main { width: min(100%, 560px); padding: clamp(28px, 6vw, 52px); border: 1px solid #e5e7eb; border-radius: 24px; background: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, .08); text-align: center; }
        .brand { display: inline-grid; width: 54px; height: 54px; place-items: center; border-radius: 16px; background: #d71920; color: white; font-size: 24px; font-weight: 900; }
        .code { margin: 26px 0 8px; color: #d71920; font-size: clamp(42px, 10vw, 64px); font-weight: 900; line-height: 1; }
        h1 { margin: 0; font-size: clamp(22px, 5vw, 30px); font-weight: 800; }
        p { margin: 14px 0 26px; color: #64748b; font-size: 15px; line-height: 1.7; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 20px; border-radius: 12px; background: #d71920; color: #fff; font-size: 14px; font-weight: 800; text-decoration: none; }
        a:hover { background: #b9151b; }
        .foot { margin-top: 28px; color: #94a3b8; font-size: 11px; }
    </style>
</head>
<body>
    <main>
        <div class="brand" aria-label="KONI Klaten">K</div>
        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
        <div class="foot">KONI Kabupaten Klaten · Sistem Informasi dan Manajemen Olahraga</div>
    </main>
</body>
</html>
