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
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                display: ['Cormorant Garamond', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                brand: {
                    red: 'var(--brand-red, #761737)',
                    black: 'var(--brand-black, #32161f)',
                    dark: 'var(--brand-dark, #4f1028)',
                    gray: '#fbf6ee',
                    cream: '#fffaf2',
                    gold: '#c39a50',
                },
            },
        },
    },

    plugins: [forms],
};
