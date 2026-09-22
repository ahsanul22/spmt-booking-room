/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: { primary: '#216EAD', primaryDark: '#0E336A', secondary: '#5EA9D8', secondaryLight: '#AFCFE4', background: '#F2F4F5', surface: '#FFFFFF', muted: '#A2A2A6', text: '#222424', danger: '#D4302C' },
      fontFamily: { sans: ['Segoe UI', 'Arial', 'sans-serif'] },
    },
  },
  plugins: [],
}
