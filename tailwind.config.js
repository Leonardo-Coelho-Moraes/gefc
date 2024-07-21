/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
   
    './templates/site/views/*.html',
  ],
  theme: {
    extend: {},
  },
  plugins: [
           require('flowbite'),

  ],
}

        