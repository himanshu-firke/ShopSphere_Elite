/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.jsx",
    "./resources/**/*.tsx",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1e40af', // Deep blue color
        'primary-light': '#3b82f6',
        'primary-dark': '#1e3a8a',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
