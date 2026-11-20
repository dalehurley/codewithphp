import { readdirSync } from 'node:fs';

const socialDirectory = '/social/';
let socialFiles: Set<string> | undefined;

export function getSeriesSlug(slug: string): string | undefined {
  return slug.match(/^series\/([^/]+)(?:\/|$)/)?.[1];
}

export function isSeriesIndex(slug: string): boolean {
  return /^series\/[^/]+$/.test(slug);
}

export function isCourseResource(slug: string): boolean {
  return /^series\/[^/]+\/.+/.test(slug);
}

export function isHomepage(slug: string): boolean {
  return slug === '' || slug === 'index';
}

export function stripChapterNumber(title: string): string {
  return title.replace(/^(?:chapter\s+)?\d+[a-z]?\s*[:–—-]\s*/i, '');
}

/**
 * Prefer a page's existing card and fall back to its course card or the site
 * card. Some lessons have no generated image, so test each candidate.
 */
export function getSocialPreview(
  slug: string,
  title: string,
  siteTitle: string,
  seriesTitle: string | undefined,
  options: { chapter?: number; socialImage?: string },
): { path: string; alt: string } {
  socialFiles ??= new Set(readdirSync('public/social'));

  const series = getSeriesSlug(slug);
  const lessonSlug = slug.split('/').at(-1);
  const routeChapter = lessonSlug?.match(/^(\d+[a-z]?)-/)?.[1];
  const chapter = options.chapter === undefined ? routeChapter : String(options.chapter).padStart(2, '0');
  const chapterPath = series && chapter && slug.includes('/chapters/')
    ? `${socialDirectory}${series}-chapter-${chapter}.jpg`
    : undefined;

  const candidates = [
    { path: options.socialImage, alt: title },
    { path: chapterPath, alt: title },
    { path: series ? `${socialDirectory}${series}-overview.jpg` : undefined, alt: seriesTitle ?? title },
  ];

  for (const { path, alt } of candidates) {
    if (path?.startsWith(socialDirectory) && socialFiles.has(path.slice(socialDirectory.length))) {
      return { path, alt };
    }
  }

  return { path: `${socialDirectory}homepage.jpg`, alt: siteTitle };
}

export function isComparisonPage(slug: string): boolean {
  return /^compare\/[^/]+$/.test(slug);
}
