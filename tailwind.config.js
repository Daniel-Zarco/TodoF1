import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                inter:  ['Inter', ...defaultTheme.fontFamily.sans],
                oswald: ['Oswald', 'sans-serif'],
                sans:   ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'f1-red':  '#E10600',
                'f1-black': '#000000',
                'f1-dark': '#0A0A0A',
                'f1-card': '#111111',
                'f1-border': '#222222',
            },
            backgroundOpacity: {
                3: '0.03',
            },
        },
    },

    plugins: [forms],
};
