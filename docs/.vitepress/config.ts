import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'PHP From Scratch',
  description: 'Learn PHP and its ecosystem from first principles to advanced.',
  base: '/PHP-From-Scratch/',
  lastUpdated: true,
  themeConfig: {
    nav: [
      { text: 'Home', link: '/' },
      {
        text: 'Series',
        items: [
          { text: 'PHP Basics', link: '/series/php-basics/' },
          { text: 'Modern PHP', link: '/series/modern-php/' }
        ]
      },
      { text: 'GitHub', link: 'https://github.com/dalehurley/PHP-From-Scratch' }
    ],
    sidebar: {
      '/series/php-basics/': [
        { text: 'Overview', link: '/series/php-basics/' },
        {
          text: 'Chapters',
          items: [
            {
              text: '01 — Getting Started',
              link: '/series/php-basics/chapters/01-getting-started'
            }
          ]
        }
      ]
    },
    socialLinks: [
      { icon: 'github', link: 'https://github.com/dalehurley/PHP-From-Scratch' }
    ],
    editLink: {
      pattern:
        'https://github.com/dalehurley/PHP-From-Scratch/edit/main/docs/:path'
    }
  },
  markdown: {
    lineNumbers: true
  }
})


