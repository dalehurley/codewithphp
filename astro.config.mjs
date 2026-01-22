import { defineConfig } from 'astro/config';
import starlight from '@astrojs/starlight';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';
import rehypeMermaid from 'rehype-mermaidjs';

// https://astro.build/config
export default defineConfig({
  site: 'https://codewithphp.com',
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
            content: '#3c8772',
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
      sidebar: [
        { label: 'Home', link: '/' },
        {
          label: 'PHP Basics',
          items: [
            {
              label: 'Overview',
              link: '/series/php-basics/',
            },
          ]
        },
      ],
    }),
  ],
  markdown: {
    remarkPlugins: [remarkMath],
    rehypePlugins: [rehypeKatex, rehypeMermaid],
  },
});
