import { copyFile, readFile } from 'node:fs/promises';
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import starlight from '@astrojs/starlight';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeKatexStylesheet from './src/lib/rehype-katex-stylesheet.mjs';
import { parse } from 'node-html-parser';
import learningLayout from './src/lib/rehype-learning-layout.mjs';
import crmRedirects from './src/lib/crm-redirects.json' with { type: 'json' };

const redirects = Object.fromEntries(Object.entries(crmRedirects).map(([from, destination]) =>
  [from, { status: 301, destination }]
));
let outputDirectory;

async function sitemapEntry(entry) {
  const pathname = decodeURIComponent(new URL(entry.url).pathname).replace(/^\//, '');
  const html = await readFile(new URL(pathname + 'index.html', outputDirectory), 'utf8');
  const head = parse(html.slice(0, html.indexOf('</head>')));
  const robots = head.querySelector('meta[name="robots"]')?.getAttribute('content') ?? '';
  if (/(?:^|[,\s])(?:noindex|none)(?:$|[,\s])/i.test(robots)) return;
  const lastmod = head.querySelector('meta[property="article:modified_time"]')?.getAttribute('content');
  return lastmod ? { ...entry, lastmod } : entry;
}

const sitemapAlias = {
  name: 'codewithphp:sitemap-alias',
  hooks: {
    'astro:config:done': ({ config }) => { outputDirectory = config.outDir; },
    'astro:build:done': ({ dir }) =>
      copyFile(new URL('sitemap-index.xml', dir), new URL('sitemap.xml', dir)),
  },
};

// https://astro.build/config
export default defineConfig({
  site: 'https://codewithphp.com',
  redirects,
  build: {
    // Limit concurrent page builds to reduce memory pressure
    concurrency: 1,
  },
  integrations: [
    starlight({
      title: 'Code with PHP',
      description: 'Free PHP and Laravel tutorials with runnable examples and hands-on projects.',
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
      favicon: '/images/brand/pinup-mark.png',
      head: [
        {
          tag: 'meta',
          attrs: {
            name: 'theme-color',
            content: '#7C3AED',
          },
        },
      ],
      customCss: [
        './src/styles/custom.css',
        './src/styles/home.css',
        './src/styles/learning.css',
      ],
      components: {
        Head: './src/Head.astro',
        Header: './src/components/Header.astro',
        Hero: './src/components/SplashHero.astro',
        PageTitle: './src/components/learning/LearningTitle.astro',
      },
      sidebar: [
        { label: 'Home', link: '/' },
        { label: 'All courses', link: '/series/' },
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
          label: 'What’s New in PHP 8.6',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/php-8-6/' },
            { label: 'PHP 8.5 to 8.6 upgrade checklist', link: '/series/php-8-6/upgrade-checklist/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/php-8-6/chapters' },
            },
          ],
        },
        {
          label: 'Agentic AI for PHP Developers',
          collapsed: true,
          items: [
            { label: 'Series Overview', link: '/series/agentic-ai-php-developers/' },
            {
              label: 'Chapters',
              autogenerate: { directory: 'series/agentic-ai-php-developers/chapters' },
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
    learningLayout(),
    sitemap({
      filter: (url) => {
        const pathname = new URL(url).pathname;
        return !redirects[pathname] && !redirects[pathname.replace(/\/$/, '')];
      },
      serialize: sitemapEntry,
    }),
    sitemapAlias,
  ],
  markdown: {
    remarkPlugins: [remarkMath],
    rehypePlugins: [rehypeKatex, rehypeKatexStylesheet],
  },
});
