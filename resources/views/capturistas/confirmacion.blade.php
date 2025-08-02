<!DOCTYPE html>
<html>
<head>
    <title>Credenciales del capturista</title>
</head>
<body>
    <h1>Capturista creado exitosamente</h1>

    <p><strong>Correo:</strong> {{ $email }}</p>
    <p><strong>Contraseña generada:</strong> {{ $password }}</p>

    <a href="{{ route('capturistas.create') }}">Crear otro operador</a>
</body>
</html>
