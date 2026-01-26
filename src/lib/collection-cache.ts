import { getCollection } from 'astro:content';

// Cache the collection entries and lookup maps to avoid reloading on every page
type DocsEntry = Awaited<ReturnType<typeof getCollection<'docs'>>>[number];

let cachedEntries: DocsEntry[] | null = null;
let slugLookup: Map<string, DocsEntry> | null = null;

/**
 * Get cached collection entries. Only loads once during build.
 */
export async function getCachedCollection() {
  if (!cachedEntries) {
    cachedEntries = await getCollection('docs');

    // Build lookup maps for O(1) access instead of O(n) find operations
    slugLookup = new Map();
    for (const entry of cachedEntries) {
      slugLookup.set(entry.slug, entry);
      slugLookup.set(entry.id, entry);
    }
  }
  return cachedEntries;
}

/**
 * Find an entry by slug efficiently using cached lookup map.
 * Returns the entry or undefined if not found.
 */
export async function findEntryBySlug(slug: string) {
  await getCachedCollection();

  if (!slugLookup) return undefined;

  // Try exact slug match first
  let entry = slugLookup.get(slug);
  if (entry) return entry;

  // Try with /index suffix for directory pages
  const indexSlug = slug === '' ? 'index' : `${slug}/index`;
  entry = slugLookup.get(indexSlug);
  if (entry) return entry;

  // Try matching by id
  entry = slugLookup.get(slug === '' ? 'index' : slug);

  return entry;
}
