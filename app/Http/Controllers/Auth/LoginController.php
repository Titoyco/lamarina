<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class LoginController extends Controller
{
    // Método para registrar un nuevo usuario
    public function register(Request $request)  
    {
        // Validar los datos
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed', // Usa password_confirmation en el form
        ]);

        $user = new User();
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->save();

        // Iniciar sesión automáticamente
        Auth::login($user);

        // Registrar la primera sucursal del usuario en la sesión
        $this->setSucursalActiva($user);

        return redirect()->route('home')->with('success', 'Usuario registrado correctamente'); // Usa el nombre de ruta
    }

    // Muestra el formulario de inicio de sesión
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Maneja el inicio de sesión
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Intentar autenticar al usuario
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();

            // Registrar la primera sucursal del usuario en la sesión
            $this->setSucursalActiva($user);

            // Autenticación exitosa
            return redirect()->intended(route('home'));
        }

        // Si la autenticación falla, redirigir de nuevo con un error
        return back()->withErrors([
            'username' => 'ERROR: Las credenciales proporcionadas son incorrectas, intente nuevamente.',
        ])->withInput($request->only('username'));
    }

    // Maneja el cierre de sesión
    public function logout(Request $request)
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
        return redirect()->route('login'); // Redirige al login
    }

    // Establecer la primera sucursal activa del usuario en la sesión
    protected function setSucursalActiva($user)
    {
        // Obtener la primera sucursal asociada al usuario
        $primerSucursal = DB::table('user_sucursales')
            ->where('user_id', $user->id)
            ->value('sucursal_id');

        // Guardar la sucursal activa en la sesión
        if ($primerSucursal) {
            Session::put('sucursal_activa', $primerSucursal);
        }
    }
}