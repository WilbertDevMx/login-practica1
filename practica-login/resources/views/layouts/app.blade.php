<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Aplicación Segura')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
    </style>
    @yield('head')
</head>
<body>

    @yield('content')

    @auth
    <script>
        const INACTIVITY_MINUTES = 15;
        let timer;

        function resetTimer() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/cerrar-sesion-ahora';
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }, INACTIVITY_MINUTES * 60 * 1000);
        }

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(evt) {
            document.addEventListener(evt, resetTimer, { passive: true });
        });

        resetTimer();
    </script>
    @endauth

    @yield('scripts')
</body>
</html>