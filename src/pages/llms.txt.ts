import type { APIRoute } from 'astro';
import { docPath, getAgentDocs, markdownPathFor } from '../lib/agent-docs';

function escapeLabel(label: string) {
  return label.replace(/[\\[\]]/g, '\\$&').replace(/\s+/g, ' ');
}

export const GET: APIRoute = async ({ site }) => {
  const entries = await getAgentDocs();
  const groups = new Map<string, typeof entries>();
  for (const entry of entries) {
    const [section, course] = entry.slug.split('/');
    const groupSlug = section === 'series' ? section + '/' + course : section;
    const group = groups.get(groupSlug) ?? [];
    group.push(entry);
    groups.set(groupSlug, group);
  }

  const lines = [
    '# Code with PHP',
    '',
    '> Free, project-based PHP and Laravel tutorials with examples and exercises.',
    '',
    'Each Markdown file links to the original HTML page. Course materials and example code are maintained in the public repository.',
    '',
    '## Start here',
    '',
    '- [All PHP courses](' + new URL('/series/', site).href + ')',
    '- [PHP resources](' + new URL('/resources/', site).href + ')',
    '- [Repository and sample code](https://github.com/dalehurley/codewithphp)'
  ];

  for (const [slug, group] of groups) {
    const hub = group.find(entry => entry.slug === slug);
    lines.push('', '## ' + (hub?.data.title ?? slug.split('/').at(-1)), '');
    for (const entry of group) {
      const target = new URL(markdownPathFor(docPath(entry)), site);
      lines.push('- [' + escapeLabel(entry.data.title) + '](' + target.href + ')');
    }
  }

  return new Response(lines.join('\n') + '\n', {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' }
  });
};
