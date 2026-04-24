/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#edf5f6',
                    100: '#d1e6e9',
                    200: '#a3cdd3',
                    300: '#739fa5',
                    400: '#5a9199',
                    500: '#40848d',
                    600: '#366f76',
                    700: '#2c5a60',
                    800: '#224549',
                    900: '#183033',
                },
                neutral: {
                    50:  '#f5f5f5',
                    100: '#e5e5e5',
                    200: '#cccccc',
                    300: '#b3b3b3',
                    400: '#999999',
                    500: '#666666',
                    600: '#4d4d4d',
                    700: '#404040',
                    800: '#333333',
                    900: '#1a1a1a',
                },
                accent: {
                    50:  '#f4f8f9',
                    100: '#e5eff0',
                    200: '#c1d7da',
                    300: '#a8c8cc',
                    400: '#8fb9be',
                    500: '#76aab0',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
