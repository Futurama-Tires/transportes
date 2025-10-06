<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Credenciales del operador</title>
    <meta name="robots" content="noindex">
</head>
<body>
    <h1>Operador creado exitosamente</h1>

    @if(session('email') && session('password'))
        <p><strong>Correo:</strong> {{ session('email') }}</p>
        <p><strong>Contraseña generada:</strong> {{ session('password') }}</p>
    @else
        <p>No hay datos de confirmación disponibles.</p>
    @endif

    <p><a href="{{ route('operadores.create') }}">Crear otro operador</a></p>
</body>
</html>
