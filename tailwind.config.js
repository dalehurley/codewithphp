/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "docs/**/*.md",
    "docs/.vitepress/theme/**/*.{js,ts,vue}",
    "docs/.vitepress/**/*.{js,ts,vue}"
  ],
  theme: {
    extend: {
      colors: {
        // Brand colors - Modern Teal
        brand: {
          DEFAULT: '#0d9488',
          light: '#14b8a6',
          lighter: '#2dd4bf',
          dark: '#0f766e',
          darker: '#115e59',
        },
        // Series-specific colors
        'php-amber': '#f59e0b',
        'php-orange': '#ea580c',
        'ai-purple': '#7c3aed',
        'ai-violet': '#8b5cf6',
        'claude-purple': '#7c3aed',
        'claude-violet': '#8b5cf6',
        'python-blue': '#0ea5e9',
        'python-cyan': '#06b6d4',
        'rails-red': '#dc2626',
        'rails-ruby': '#ef4444',
        'neutral-gray': '#64748b',
        'bg-light': '#f8fafc',
      },
      backgroundImage: {
        'gradient-brand': 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #0d9488 100%)',
        'gradient-brand-button': 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)',
        'gradient-brand-button-hover': 'linear-gradient(135deg, #0284c7 0%, #0891b2 100%)',
        'gradient-teal': 'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)',
        'gradient-php': 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)',
        'gradient-ai': 'linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%)',
        'gradient-claude': 'linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%)',
        'gradient-python': 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)',
        'gradient-rails': 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)',
        'gradient-beginner': 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
        'gradient-intermediate': 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)',
        'gradient-advanced': 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
        'gradient-progress': 'linear-gradient(135deg, #0d9488 0%, #14b8a6 100%)',
        'gradient-progress-complete': 'linear-gradient(90deg, #10b981 0%, #34d399 50%, #10b981 100%)',
      },
      boxShadow: {
        'mission-hero': '0 20px 60px rgba(14, 165, 233, 0.3)',
        'brand-button': '0 4px 14px rgba(14, 165, 233, 0.4)',
        'brand-button-hover': '0 6px 20px rgba(14, 165, 233, 0.5)',
      },
    },
  },
  darkMode: 'class',
}
