/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './storage/framework/views/*.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        'primary': '#0D532D',
        'primary-light': '#1A6E3C',
      },
      backgroundImage: {
        'primary-gradient': 'linear-gradient(180deg, #0D532D 0%, #1A6E3C 100%)',
      },
    },
  },
  plugins: [],
}
