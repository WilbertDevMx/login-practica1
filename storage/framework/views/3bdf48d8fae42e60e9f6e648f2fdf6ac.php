<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Dos Factores</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>
    <h2>Verificación de Dos Factores</h2>

    <?php if($isNew): ?>
        <p>Escanea este código QR con Google Authenticator:</p>
        <img src="<?php echo e($qrCode); ?>" alt="Código QR para Google Authenticator">
        <p>O introduce manualmente esta clave secreta: <strong><?php echo e($secret); ?></strong></p>
    <?php endif; ?>

    <p>Ingresa el código de 6 dígitos de tu aplicación autenticadora:</p>

    <form method="POST" action="<?php echo e(route('2fa.verify')); ?>">
        <?php echo csrf_field(); ?>
        <input type="text" name="one_time_password" placeholder="000000" required autofocus>
        <?php $__errorArgs = ['one_time_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color:red;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <button type="submit">Verificar</button>
    </form>
</body>
</html><?php /**PATH K:\proyectos laravel\practica-login\resources\views/auth/2fa_verify.blade.php ENDPATH**/ ?>