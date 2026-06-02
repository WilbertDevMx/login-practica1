<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Mi Aplicación Segura</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            text-align: center;
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        .user-avatar {
            background: #667eea;
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .user-info {
            background: #f7f9fc;
            padding: 1rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            text-align: left;
        }

        .user-info p {
            margin: 8px 0;
            color: #2c3e50;
        }

        .user-info strong {
            color: #667eea;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            width: 100%;
            font-weight: bold;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .logout-btn:active {
            transform: scale(0.98);
        }

        .footer {
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #95a5a6;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="dashboard-card">
        <div class="user-avatar">
            <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

        </div>
        <h1>Dashboard</h1>

        <?php if(session('success')): ?>
            <div class="alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="user-info">
            <p><strong>👤 Nombre:</strong> <?php echo e(Auth::user()->name); ?></p>
            <p><strong>📧 Email:</strong> <?php echo e(Auth::user()->email); ?></p>
            <p><strong>🆔 ID:</strong> <?php echo e(Auth::user()->id); ?></p>
            <p><strong>🔐 Miembro desde:</strong> <?php echo e(Auth::user()->created_at->format('d/m/Y H:i')); ?></p>
        </div>

        <p style="margin: 1rem 0; color: #555;">
            ✅ Esta es tu área privada. Solo tú puedes verla.
        </p>

        <!-- Formulario de cierre de sesión (método POST con CSRF) -->
        <form method="POST" action="/cerrar-sesion-ahora">
            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
            <button type="submit">Cerrar sesión (hardcoded)</button>
        </form>

        <div class="footer">
            &copy; <?php echo e(date('Y')); ?> - Aplicación con buenas prácticas de seguridad
        </div>
    </div>
</body>
</html><?php /**PATH K:\proyectos laravel\practica-login\resources\views/dashboard.blade.php ENDPATH**/ ?>