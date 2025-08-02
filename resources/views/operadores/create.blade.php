<!DOCTYPE html>
<html>
<head>
    <title>Crear operador</title>
</head>
<body>
    <h1>Registrar nuevo operador</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('operadores.store') }}">
        @csrf

        <label>Nombre:</label>
        <input type="text" name="nombre" required><br>

        <label>Apellido paterno:</label>
        <input type="text" name="apellido_paterno" required><br>

        <label>Apellido materno:</label>
        <input type="text" name="apellido_materno"><br>

        <label>Email (@futuramatiresmx.com):</label>
        <input type="email" name="email" required><br>

        <button type="submit">Crear operador</button>
    </form>
</body>

@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
</html>
