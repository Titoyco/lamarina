<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        $usuarios = User::all();
        return view('usuarios.index', compact('usuarios'));
    }

    // Formulario para agregar usuario
    public function create()
    {
        return view('usuarios.create');
    }

    // Guardar usuario
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User();
        $user->username = $request->name;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado con éxito.');
    }

    // Borrar usuario
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado con éxito.');
    }

    // Formulario para cambiar clave
    public function editPassword(User $user)
    {
        // Solo el propio usuario o un administrador puede cambiar la clave
        if (Auth::id() == $user->id || Auth::user()->username == "admin") {
             return view('usuarios.edit-password', compact('user'));
        } else {
            abort(403, 'No autorizado');
        }

       
    }

// Actualizar clave
public function updatePassword(Request $request, User $user)
{
    // Validación de la clave
    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ], [
        'password.required' => 'La clave es obligatoria.',
        'password.string' => 'La clave debe ser una cadena de texto.',
        'password.min' => 'La clave debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las claves no coinciden.',
    ]);
    // Solo permitir si es el propio usuario o un administrador
    if (Auth::id() !== $user->id && Auth::user()->username !== "admin") {
        abort(403, 'No autorizado');
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return redirect()->route('usuarios.index')->with('success', 'Clave actualizada con éxito.');
}
}