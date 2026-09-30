import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'body': ['1rem', { lineHeight: '1.5rem' }],          // 16px (Baseline body text per DESIGN.md)
                'body-sm': ['0.875rem', { lineHeight: '1.25rem' }],   // 14px
                'caption': ['0.75rem', { lineHeight: '1rem' }],       // 12px
                'heading-sm': ['1.125rem', { lineHeight: '1.5rem', fontWeight: '600' }], // 18px
                'heading-md': ['1.25rem', { lineHeight: '1.75rem', fontWeight: '700' }],  // 20px
                'heading-lg': ['1.5rem', { lineHeight: '2rem', fontWeight: '700' }],      // 24px
                'heading-xl': ['1.875rem', { lineHeight: '2.25rem', fontWeight: '800' }], // 30px
            },
            borderRadius: {
                'radius-sm': '0.375rem',  // 6px - subtle soft radius
                'radius-md': '0.625rem',  // 10px - default component soft radius
                'radius-lg': '0.875rem',  // 14px - card/panel soft radius
                'radius-xl': '1rem',      // 16px - outer container max soft radius
            },
            boxShadow: {
                'subtle': '0 1px 2px 0 rgba(0, 0, 0, 0.04), 0 1px 1px 0 rgba(0, 0, 0, 0.02)',
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)',
                'popover': '0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05)',
            },
            colors: {
                primary: {
                    DEFAULT: 'rgb(var(--color-primary) / <alpha-value>)',
                    hover: 'rgb(var(--color-primary-hover) / <alpha-value>)',
                    light: 'rgb(var(--color-primary-light) / <alpha-value>)',
                    fg: 'rgb(var(--color-primary-fg) / <alpha-value>)',
                },
                secondary: {
                    DEFAULT: 'rgb(var(--color-secondary) / <alpha-value>)',
                    hover: 'rgb(var(--color-secondary-hover) / <alpha-value>)',
                    light: 'rgb(var(--color-secondary-light) / <alpha-value>)',
                },
                background: 'rgb(var(--color-background) / <alpha-value>)',
                surface: {
                    DEFAULT: 'rgb(var(--color-surface) / <alpha-value>)',
                    muted: 'rgb(var(--color-surface-muted) / <alpha-value>)',
                },
                text: {
                    DEFAULT: 'rgb(var(--color-text) / <alpha-value>)',
                    muted: 'rgb(var(--color-text-muted) / <alpha-value>)',
                },
                border: {
                    DEFAULT: 'rgb(var(--color-border) / <alpha-value>)',
                    subtle: 'rgb(var(--color-border-subtle) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--color-success) / <alpha-value>)',
                    light: 'rgb(var(--color-success-light) / <alpha-value>)',
                    text: 'rgb(var(--color-success-text) / <alpha-value>)',
                },
                warning: {
                    DEFAULT: 'rgb(var(--color-warning) / <alpha-value>)',
                    light: 'rgb(var(--color-warning-light) / <alpha-value>)',
                    text: 'rgb(var(--color-warning-text) / <alpha-value>)',
                },
                danger: {
                    DEFAULT: 'rgb(var(--color-danger) / <alpha-value>)',
                    light: 'rgb(var(--color-danger-light) / <alpha-value>)',
                    text: 'rgb(var(--color-danger-text) / <alpha-value>)',
                },
                info: {
                    DEFAULT: 'rgb(var(--color-info) / <alpha-value>)',
                    light: 'rgb(var(--color-info-light) / <alpha-value>)',
                    text: 'rgb(var(--color-info-text) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
