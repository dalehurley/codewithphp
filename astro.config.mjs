import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import starlight from '@astrojs/starlight';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';

// https://astro.build/config
export default defineConfig({
  site: 'https://codewithphp.com',
  build: {
    // Limit concurrent page builds to reduce memory pressure
    concurrency: 1,
  },
  integrations: [
    starlight({
      title: 'Code with PHP',
      description: 'Learn PHP and its ecosystem from first principles to advanced.',
      social: [
        {
          icon: 'github',
          label: 'GitHub',
          href: 'https://github.com/dalehurley/codewithphp',
        },
      ],
      editLink: {
        baseUrl: 'https://github.com/dalehurley/codewithphp/edit/main/',
      },
      favicon: '/favicon.ico',
      head: [
        {
          tag: 'meta',
          attrs: {
            name: 'theme-color',
            content: '#7C3AED',
          },
        },
        {
          tag: 'link',
          attrs: {
            rel: 'stylesheet',
            href: 'https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css',
          },
        },
      ],
      customCss: [
        './src/styles/custom.css',
      ],
      components: {
        Head: './src/Head.astro',
      },
      sidebar: [
        { label: 'Home', link: '/' },
        {
          label: 'PHP Basics',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/php-basics/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/php-basics/chapters' },
            },
          ],
        },
        {
          label: 'AI/ML for PHP Developers',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/ai-ml-php-developers/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/ai-ml-php-developers/chapters' },
            },
          ],
        },
        {
          label: 'Build a CRM with Laravel 12',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/build-crm-laravel-12/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/build-crm-laravel-12/chapters' },
            },
          ],
        },
        {
          label: 'Claude for PHP Developers',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/claude-php-developers/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/claude-php-developers/chapters' },
            },
          ],
        },
        {
          label: 'Data Science for PHP Developers',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/data-science-php-developers/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/data-science-php-developers/chapters' },
            },
          ],
        },
        {
          label: 'PHP Algorithms',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/php-algorithms/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/php-algorithms/chapters' },
            },
          ],
        },
        {
          label: 'PHP for Java Developers',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/php-for-java-developers/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/php-for-java-developers/chapters' },
            },
          ],
        },
        {
          label: 'PHP for TypeScript Developers',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/php-typescript-developers/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/php-typescript-developers/chapters' },
            },
          ],
        },
        {
          label: 'Python Developers Love PHP/Laravel',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/python-developers-love-php-laravel/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/python-developers-love-php-laravel/chapters' },
            },
          ],
        },
        {
          label: 'Rails Developers Love Laravel',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/rails-developers-love-laravel/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/rails-developers-love-laravel/chapters' },
            },
          ],
        },
      ],
    }),
    sitemap(),
  ],
  markdown: {
    remarkPlugins: [remarkMath],
    rehypePlugins: [rehypeKatex],
  },
});
