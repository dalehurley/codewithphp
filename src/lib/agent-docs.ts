import type { CollectionEntry } from 'astro:content';
import { getCachedCollection } from './collection-cache';
import { isComparisonPage } from './seo';

type Doc = CollectionEntry<'docs'>;

export function markdownPathFor(pathname: string) {
  return pathname.replace(/\/$/, '') + '.md';
}

export function hasMarkdownAlternate(entry: Pick<Doc, 'id' | 'slug' | 'body' | 'data'>) {
  const headExcludesIndexing = entry.data.head?.some(({ tag, attrs }) =>
    tag === 'meta' && attrs?.name === 'robots' &&
    /\b(noindex|none)\b/i.test(String(attrs?.content ?? ''))
  );

  return (entry.slug.startsWith('series/') || entry.slug === 'compare' || isComparisonPage(entry.slug)) &&
    entry.id.endsWith('.md') &&
    Boolean(entry.body.trim()) &&
    !entry.data.draft &&
    !entry.data.noindex &&
    !headExcludesIndexing;
}

export async function getAgentDocs() {
  const entries = await getCachedCollection();
  return entries.filter(hasMarkdownAlternate).sort((a, b) => a.slug.localeCompare(b.slug));
}

export function docPath(entry: Doc) {
  return '/' + entry.slug + '/';
}

export function renderAgentMarkdown(entry: Doc, site: URL) {
  const canonical = new URL(docPath(entry), site);
  const lines = [
    '# ' + entry.data.title,
    '',
    '[Original page](' + canonical.href + ')',
    ''
  ];
  if (entry.data.description) lines.push('> ' + entry.data.description, '');
  return lines.join('\n') + '\n' + entry.body.trim() + '\n';
}
