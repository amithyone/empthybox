/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Proxima Nova', 'Montserrat', 'system-ui', 'sans-serif'],
        display: ['Proxima Nova', 'Montserrat', 'system-ui', 'sans-serif'],
      },
      colors: {
        dark: {
          100: '#1a1a1a',
          200: '#2d2d2d',
          300: '#3d3d3d',
          400: '#4a4a4a',
        },
        red: {
          accent: '#ef4444',
          dark: '#dc2626',
          light: '#f87171',
        },
        yellow: {
          accent: '#fbbf24',
          dark: '#f59e0b',
          light: '#fcd34d',
        }
      }
    },
  },
  plugins: [],
}


