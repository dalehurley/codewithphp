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
              text: '00 — Setting Up Your Development Environment',
              link: '/series/php-basics/chapters/00-setting-up-your-development-environment'
            },
            {
              text: '01 — Your First PHP Script',
              link: '/series/php-basics/chapters/01-your-first-php-script'
            },
            {
              text: '02 — Variables, Data Types, and Constants',
              link: '/series/php-basics/chapters/02-variables-data-types-and-constants'
            },
            {
              text: '03 — Control Structures',
              link: '/series/php-basics/chapters/03-control-structures'
            },
            {
              text: '04 — Understanding and Using Functions',
              link: '/series/php-basics/chapters/04-understanding-and-using-functions'
            },
            {
              text: '05 — Handling HTML Forms and User Input',
              link: '/series/php-basics/chapters/05-handling-html-forms-and-user-input'
            },
            {
              text: '06 — Deep Dive into Arrays',
              link: '/series/php-basics/chapters/06-deep-dive-into-arrays'
            },
            {
              text: '07 — Mastering String Manipulation',
              link: '/series/php-basics/chapters/07-mastering-string-manipulation'
            },
            {
              text: '08 — Introduction to Object-Oriented Programming',
              link: '/series/php-basics/chapters/08-introduction-to-object-oriented-programming'
            },
            {
              text: '09 — OOP: Inheritance, Abstract Classes, and Interfaces',
              link: '/series/php-basics/chapters/09-oop-inheritance-abstract-classes-and-interfaces'
            },
            {
              text: '10 — OOP: Traits and Namespaces',
              link: '/series/php-basics/chapters/10-oop-traits-and-namespaces'
            },
            {
              text: '11 — Error and Exception Handling',
              link: '/series/php-basics/chapters/11-error-and-exception-handling'
            },
            {
              text: '12 — Dependency Management with Composer',
              link: '/series/php-basics/chapters/12-dependency-management-with-composer'
            },
            {
              text: '13 — Working with the Filesystem',
              link: '/series/php-basics/chapters/13-working-with-the-filesystem'
            },
            {
              text: '14 — Interacting with Databases using PDO',
              link: '/series/php-basics/chapters/14-interacting-with-databases-using-pdo'
            },
            {
              text: '15 — Managing State with Sessions and Cookies',
              link: '/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies'
            },
            {
              text: '16 — Writing Better Code with PSR-1 and PSR-12',
              link: '/series/php-basics/chapters/16-writing-better-code-with-psr-1-and-psr-12'
            },
            {
              text: '17 — Building a Basic HTTP Router',
              link: '/series/php-basics/chapters/17-building-a-basic-http-router'
            },
            {
              text: '18 — Project: Structuring a Simple Application',
              link: '/series/php-basics/chapters/18-project-structuring-a-simple-application'
            },
            {
              text: '19 — Project: Building a Simple Blog',
              link: '/series/php-basics/chapters/19-project-building-a-simple-blog'
            },
            {
              text: '20 — A Gentle Introduction to Laravel',
              link: '/series/php-basics/chapters/20-a-gentle-introduction-to-laravel'
            },
            {
              text: '21 — A Gentle Introduction to Symfony',
              link: '/series/php-basics/chapters/21-a-gentle-introduction-to-symfony'
            },
            {
              text: '22 — What to Learn Next',
              link: '/series/php-basics/chapters/22-what-to-learn-next'
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


