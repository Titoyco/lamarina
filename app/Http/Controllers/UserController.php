<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra la lista de operadores del sistema.
     * El superusuario secreto NUNCA aparece en el panel de administración bajo ninguna circunstancia.
     */
    public function index()
    {
        $usuarios = User::with('sucursal')
            ->where('username', '!=', 'superuser')
            ->orderBy('name')
            ->get();

        return view('sistema.usuarios.index', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo operador.
     */
    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        return view('sistema.usuarios.create', compact('sucursales'));
    }

    /**
     * Almacena un nuevo operador en la base de datos con clave encriptada.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:191',
            'username'    => [
                'required',
                'string',
                'max:191',
                'alpha_dash',
                Rule::notIn(['superuser']),
                'unique:users,username',
            ],
            'password'    => 'required|string|min:4',
            'id_sucursal' => 'required|exists:sucursales,id',
            'is_admin'    => 'nullable|boolean',
        ], [
            'username.not_in'     => 'Ese nombre de usuario está reservado por el sistema.',
            'username.unique'     => 'El nombre de usuario ya está registrado por otro operador.',
            'username.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',
            'password.min'        => 'La clave debe tener como mínimo 4 caracteres.',
        ]);

        User::create([
            'name'        => trim($request->name),
            'username'    => strtolower(trim($request->username)),
            'password'    => Hash::make($request->password),
            'id_sucursal' => $request->id_sucursal,
            'is_admin'    => $request->boolean('is_admin') ? 1 : 0,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Operador creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar el perfil del usuario.
     */
    public function edit(string $id)
    {
        $usuario = User::where('id', $id)
            ->where('username', '!=', 'superuser')
            ->firstOrFail();

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('sistema.usuarios.edit', compact('usuario', 'sucursales'));
    }

    /**
     * Actualiza el perfil del usuario (nombre, nombre de usuario, clave opcional, sucursal, rol).
     */
    public function update(Request $request, string $id)
    {
        $usuario = User::where('id', $id)
            ->where('username', '!=', 'superuser')
            ->firstOrFail();

        $request->validate([
            'name'        => 'required|string|max:191',
            'username'    => [
                'required',
                'string',
                'max:191',
                'alpha_dash',
                Rule::notIn(['superuser']),
                Rule::unique('users', 'username')->ignore($usuario->id),
            ],
            'password'    => 'nullable|string|min:4',
            'id_sucursal' => 'required|exists:sucursales,id',
            'is_admin'    => 'nullable|boolean',
        ], [
            'username.not_in'     => 'Ese nombre de usuario está reservado por el sistema.',
            'username.unique'     => 'El nombre de usuario ya está registrado por otro operador.',
            'username.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',
            'password.min'        => 'La clave debe tener como mínimo 4 caracteres.',
        ]);

        $usuario->name = trim($request->name);
        $usuario->username = strtolower(trim($request->username));
        $usuario->id_sucursal = $request->id_sucursal;
        $usuario->is_admin = $request->boolean('is_admin') ? 1 : 0;

        // Si se ingresó una nueva clave, encriptarla con Hash::make()
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', "Perfil del usuario {$usuario->username} actualizado exitosamente.");
    }

    /**
     * Elimina a un operador del sistema.
     */
    public function destroy(string $id)
    {
        $usuario = User::where('id', $id)
            ->where('username', '!=', 'superuser')
            ->firstOrFail();

        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')->with('error', 'No puede eliminar su propia cuenta en uso.');
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', "El operador {$nombre} ha sido eliminado.");
    }
}