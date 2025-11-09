/** @type {import('tailwindcss').Config} */


module.exports = {
  content: [
    "./src/pages/**/*.{php,html}",
    "./src/pages/**/**/*.{php,html}",
    "./src/includes/**/*.{php,html}",
    "./src/includes/**/**/*.{php,html}",
  ],
  safelist: [
    "lg:ml-16",
    "lg:ml-64",
    "lg:block",
    "bg-green-100",
    "text-green-700",
    "bg-green-100",
    "text-green-700",
    "bg-red-100",
    "text-red-700",

    // Semua kombinasi warna gg- untuk bg, text, border, ring, hover, dan gradient
    {
      // mencakup semua kombinasi dinamis (bg, text, border, ring, hover, focus)
      pattern: /^(bg|text|border|ring|from|via|to|hover:bg|hover:text|hover:border|focus:bg|focus:text|focus:border|focus:ring)-gg-(primary|secondary|accent|neutral|base|muted)$/,
    },
  ],
  theme: {
    extend: {
      colors: {
        gg: {
          primary: 'rgb(16 185 129)',// warna utama brand (misalnya hijau tua)
          secondary: '#4CAF50',   // warna aksen / hover
          accent: '#F77F00',      // warna sorotan / promo / alert
          neutral: '#1D1D1D',     // warna teks utama / navbar
          base: '#FFFFFF',        // warna dasar (white / surface)
          muted: '#F8EDEB',       // warna lembut untuk background
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        poppins: ['Poppins', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 2px 8px rgba(0,0,0,0.05)',
        medium: '0 4px 12px rgba(0,0,0,0.1)',
      },
      borderRadius: {
        xl: '1rem',
        '2xl': '1.25rem',
      },
    },
  },
  plugins: [],
}

