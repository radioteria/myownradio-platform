/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/views/**/*.{js,ts,jsx,tsx,mdx}',
    './src/layouts/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/modules/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
      },
      colors: {
        'morblue-50': '#E9EAEB',
        'morblue-100': '#BDBFC4',
        'morblue-200': '#8D9098',
        'morblue-300': '#454A58',
        'morblue-400': '#303545',
        'morblue-500': '#2C3142',
        'morblue-600': '#292E3F',
        'morblue-700': '#272d43',
        'morblue-800': '#23283A',
        'morblue-900': '#202434',
        'morblue-950': '#1e202d',
        'morblue-1000': '#13141c',
      },
      gridTemplateColumns: {
        'playlist-item':
          '[index] 24px [title] 6fr [time] 46px [artist] 4fr [album] 3fr [etc] minmax(120px,1fr);',
      },
    },
    fontSize: {
      xs: '8pt',
      sm: '9pt',
      base: '10pt',
      md: '11pt',
      lg: '12pt',
      xl: '14pt',
      '2xl': '16pt',
      '3xl': '20pt',
    },
  },
  plugins: [],
}
