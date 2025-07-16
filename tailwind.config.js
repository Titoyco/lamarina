/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // Usar 'class' para habilitar clases de modo oscuro
  content: [
    './resources/**/*.blade.php', // Archivos Blade
    './resources/**/*.js',        // Archivos JavaScript
    './resources/**/*.vue',       // Archivos Vue (si usas Vue)
    './resources/css/**/*.css',   // Archivos CSS (si usas CSS adicional)
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

