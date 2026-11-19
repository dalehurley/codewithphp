import type { APIRoute } from 'astro';
import { docPath, getAgentDocs, markdownPathFor } from '../lib/agent-docs';

function escapeLabel(label: string) {
  return label.replace(/[\\[\]]/g, '\\$&').replace(/\s+/g, ' ');
}

export const GET: APIRoute = async ({ site }) => {
  const entries = await getAgentDocs();
  const groups = new Map<string, typeof entries>();
  for (const entry of entries) {
    const course = entry.slug.split('/')[1];
    const group = groups.get(course) ?? [];
    group.push(entry);
    groups.set(course, group);
  }

  const lines = [
    '# Code with PHP',
    '',
    '> Free, project-based PHP and Laravel tutorials with examples and exercises.',
    '',
    'Each Markdown file links to the original HTML lesson. Course materials and example code are maintained in the public repository.',
    '',
    '## Start here',
    '',
    '- [All PHP courses](' + new URL('/series/', site).href + ')',
    '- [Repository and sample code](https://github.com/dalehurley/codewithphp)'
  ];

  for (const [slug, group] of groups) {
    const hub = group.find(entry => entry.slug === 'series/' + slug);
    lines.push('', '## ' + (hub?.data.title ?? slug), '');
    for (const entry of group) {
      const target = new URL(markdownPathFor(docPath(entry)), site);
      lines.push('- [' + escapeLabel(entry.data.title) + '](' + target.href + ')');
    }
  }

  return new Response(lines.join('\n') + '\n', {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' }
  });
};
