<!DOCTYPE html>
<html>
<head>
    <title>Credenciales del operador</title>
</head>
<body>
    <h1>Operador creado exitosamente</h1>

    <p><strong>Correo:</strong> {{ $email }}</p>
    <p><strong>Contraseña generada:</strong> {{ $password }}</p>

    <a href="{{ route('operadores.create') }}">Crear otro operador</a>
</body>
</html>
