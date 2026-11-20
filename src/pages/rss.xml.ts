import type { APIRoute } from 'astro';
import updates from '../data/site-updates.json';

const xmlCharacters: Record<string, string> = {
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&apos;',
};

function escapeXml(value: string) {
  return value.replace(/[&<>"']/g, (character) => xmlCharacters[character]);
}

export const GET: APIRoute = ({ site }) => {
  const updatesUrl = new URL('/updates/', site).href;
  const feedUrl = new URL('/rss.xml', site).href;
  const items = updates.flatMap(({ date, entries }) => entries.map(({ path, title, description }) => {
    const url = escapeXml(new URL(path, site).href);
    return `    <item>
      <title>${escapeXml(title)}</title>
      <link>${url}</link>
      <guid isPermaLink="false">${url}#update-${date}</guid>
      <pubDate>${new Date(date).toUTCString()}</pubDate>
      <description>${escapeXml(description)}</description>
    </item>`;
  }));

  return new Response(`<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Code with PHP</title>
    <link>${escapeXml(updatesUrl)}</link>
    <description>New and substantially revised PHP and Laravel lessons.</description>
    <language>en</language>
    <atom:link href="${escapeXml(feedUrl)}" rel="self" type="application/rss+xml" />
${items.join('\n')}
  </channel>
</rss>
`, { headers: { 'Content-Type': 'application/rss+xml; charset=utf-8' } });
};
