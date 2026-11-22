import { readFileSync, statSync } from 'node:fs';
import { resolve, join } from 'node:path';
import { parse } from 'node-html-parser';

const root = resolve(process.argv[2] ?? 'dist');
const origin = 'https://codewithphp.com';
const absolute = new URL(origin);
const failures = new Set();
const headings = new Map();
const check = (condition, message) => {
  if (!condition) failures.add(message);
};

function findOutput(url) {
  const path = decodeURIComponent(url.pathname).replace(/^\/+/, '');
  const candidates = [join(root, path), join(root, path, 'index.html')];
  return candidates.find(file => statSync(file, { throwIfNoEntry: false })?.isFile());
}

function readOutput(url) {
  const file = findOutput(url);
  if (!file) throw new Error('Missing build output for ' + url.href);
  return readFileSync(file, 'utf8');
}

function canonicalLinks(html) {
  return html.querySelectorAll('link[rel="canonical"]').map(node => node.getAttribute('href'));
}

function sameSiteLink(value, from) {
  try {
    const url = new URL(value, from);
    return url.origin === origin ? url : null;
  } catch {
    return null;
  }
}

function xmlLocations(xml) {
  return [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(match => new URL(match[1], absolute));
}

function checkComparisonExamples(pathname, markdownSource) {
  const comparison = pathname.match(/^\/compare\/(php-vs-[a-z0-9-]+)\/$/);
  if (!comparison) return;

  const slug = comparison[1];
  const downloadRoot = 'https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/' + slug + '/';
  const content = markdownSource.replace(/\r\n/g, '\n');
  const filenames = new Set(
    [...content.matchAll(/\]\((https:\/\/raw\.githubusercontent\.com\/[^)\s]+)\)/g)]
      .map(match => match[1])
      .filter(url => url.startsWith(downloadRoot))
      .map(url => url.slice(downloadRoot.length))
  );
  const examples = [...content.matchAll(/^```[\w-]+ title="([\w-][\w.-]*\.[a-z0-9]+)"\n([\s\S]*?)^```$/gm)];

  for (const [, filename, snippet] of examples) {
    check(filenames.delete(filename), 'Missing or repeated download for ' + pathname + filename);
    const source = new URL('../code/comparisons/' + slug + '/' + filename, import.meta.url);
    const file = statSync(source, { throwIfNoEntry: false })?.isFile()
      ? readFileSync(source, 'utf8').replace(/\r\n/g, '\n')
      : null;
    check(file === snippet, 'Comparison example differs from its source: ' + pathname + filename);
  }
  for (const filename of filenames) {
    check(false, 'Comparison download has no named code block: ' + pathname + filename);
  }
}

const index = readOutput(new URL('/sitemap-index.xml', absolute));
const sitemapUrls = xmlLocations(index).flatMap(url => xmlLocations(readOutput(url)));
const published = new Set(sitemapUrls.map(url => url.href));
check(sitemapUrls.length > 0, 'The sitemap is empty.');
check(sitemapUrls.length === published.size, 'The sitemap repeats page URLs.');
check(index === readOutput(new URL('/sitemap.xml', absolute)), 'The legacy sitemap differs from the sitemap index.');
const robots = readOutput(new URL('/robots.txt', absolute));
check(robots.includes('Sitemap: ' + origin + '/sitemap-index.xml'), 'robots.txt does not name the sitemap index.');

const llmsText = readOutput(new URL('/llms.txt', absolute));
const markdown = new Set([...llmsText.matchAll(/\((https:\/\/codewithphp\.com\/[^)\s]+\.md)\)/g)].map(match => match[1]));
check(markdown.size > 0, 'llms.txt does not list any Markdown documents.');

for (const url of sitemapUrls) {
  check(url.origin === origin, 'Sitemap URL uses an unexpected origin: ' + url.href);
  const html = parse(readOutput(url));
  const canonicals = canonicalLinks(html);
  check(canonicals.length === 1 && canonicals[0] === url.href, 'Invalid canonical at ' + url.pathname);
  const title = html.querySelector('title')?.text;
  check(Boolean(title), 'Missing title at ' + url.pathname);
  if (title) {
    check(!headings.has(title), 'Duplicate title on ' + headings.get(title) + ' and ' + url.pathname);
    headings.set(title, url.pathname);
  }
  const descriptions = html.querySelectorAll('meta[name="description"]');
  check(descriptions.length === 1 && Boolean(descriptions[0]?.getAttribute('content')), 'Invalid description at ' + url.pathname);
  check(html.querySelectorAll('h1').length === 1, 'Expected one H1 at ' + url.pathname);
  const robotsMeta = html.querySelectorAll('meta[name="robots"]');
  check(robotsMeta.length === 1 && !/\b(noindex|none)\b/.test(robotsMeta[0]?.getAttribute('content') ?? ''), 'Unexpected index directive at ' + url.pathname);
  check(!html.querySelector('meta[http-equiv="refresh"]'), 'A redirect is in the sitemap: ' + url.pathname);

  const hasMath = Boolean(html.querySelector('.katex'));
  const mathStylesheets = html.querySelectorAll('link[href*="/katex.min.css"]');
  check(mathStylesheets.length === Number(hasMath), 'Unexpected math stylesheet at ' + url.pathname);

  const image = html.querySelector('meta[property="og:image"]')?.getAttribute('content');
  const asset = image && sameSiteLink(image, url);
  check(asset && findOutput(asset), 'Missing social image at ' + url.pathname + ': ' + image);

  for (const tag of html.querySelectorAll('script[type="application/ld+json"]')) {
    const item = JSON.parse(tag.rawText);
    if (item['@type'] !== 'BreadcrumbList') continue;
    for (const crumb of item.itemListElement) {
      check(published.has(crumb.item), 'Invalid breadcrumb at ' + url.pathname + ': ' + crumb.item);
    }
  }

  const alternate = html.querySelector('link[rel="alternate"][type="text/markdown"]')?.getAttribute('href');
  if (alternate) {
    const href = new URL(alternate, url).href;
    check(markdown.has(href), 'Unlisted Markdown alternate at ' + url.pathname + ': ' + href);
    markdown.delete(href);
    const markdownSource = readOutput(new URL(href));
    check(markdownSource.includes('](' + url.href + ')'), 'Markdown alternate omits its source: ' + href);
    checkComparisonExamples(url.pathname, markdownSource);
  }

  for (const link of html.querySelectorAll('a[href]')) {
    const href = link.getAttribute('href');
    if (!href) continue;
    const target = sameSiteLink(href, url);
    if (target) check(findOutput(target), 'Broken link on ' + url.pathname + ': ' + target.pathname);
  }
}
check(markdown.size === 0, 'Markdown files without a matching HTML alternate: ' + [...markdown].join(', '));

const redirects = JSON.parse(readFileSync(new URL('../src/lib/crm-redirects.json', import.meta.url), 'utf8'));
for (const [from, to] of Object.entries(redirects)) {
  const source = new URL(from, absolute);
  source.pathname = source.pathname.replace(/\/?$/, '/');
  const destination = new URL(to, absolute);
  const page = parse(readOutput(source));
  const refresh = page.querySelector('meta[http-equiv="refresh"]')?.getAttribute('content');
  check(!published.has(source.href), 'A retired URL is in the sitemap: ' + from);
  check(published.has(destination.href), 'Missing canonical redirect destination: ' + to);
  const target = refresh?.match(/^0;\s*url\s*=\s*(.+)$/i)?.[1];
  check(target && new URL(target, source).href === destination.href, 'Missing immediate redirect for ' + from);
}

const notFound = parse(readOutput(new URL('/404.html', absolute)));
check(notFound.querySelectorAll('a[href]').some(link => link.getAttribute('href') === '/'), 'The 404 page needs a home link.');

if (failures.size) {
  console.error([...failures].join('\n'));
  process.exitCode = 1;
} else {
  console.log('Verified ' + sitemapUrls.length + ' indexable pages, internal links, previews, Markdown and redirect targets.');
}
