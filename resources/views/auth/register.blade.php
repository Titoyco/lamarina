<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <span class="">Registrar Usuario Nuevo:</span>
    <br>
    <form method="POST" action="{{ route('validar-registro') }}">
        @csrf
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <button type="submit">Register</button>
    </form>
</body>
</html>