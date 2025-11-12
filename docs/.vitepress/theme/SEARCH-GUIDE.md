# VitePress Local Search Guide

This guide explains the enhanced local search functionality.

## Overview

The site uses VitePress's built-in **local search** feature, which:
- Indexes all content during build time
- Provides instant, client-side search (no server required)
- Works offline after initial load
- Supports fuzzy matching and prefix search
- Respects content structure (titles, headings, text)

## How to Use

### Opening Search

**Keyboard shortcuts:**
- Press `/` or `Cmd/Ctrl + K` to open search
- Start typing to search immediately

**Mouse:**
- Click the "Search" button in the navigation bar

### Search Features

1. **Fuzzy Matching**
   - Typo-tolerant: "aray" finds "array"
   - Configurable fuzziness (0.2 = 20% tolerance)

2. **Prefix Search**
   - "func" matches "function", "functional", etc.
   - Great for quick lookups

3. **Weighted Results**
   - Title matches: 4x boost (most important)
   - Heading matches: 3x boost
   - Body text matches: 2x boost

4. **Detailed View**
   - Shows context around matches
   - Highlights matching terms
   - Displays page hierarchy (Series > Chapter)

### Keyboard Navigation

While search is open:
- `↑` / `↓` - Navigate through results
- `Enter` - Open selected result
- `Esc` - Close search modal

## Configuration

Search is configured in `docs/.vitepress/config.ts`:

```typescript
search: {
  provider: 'local',
  options: {
    detailedView: true,  // Show context in results
    miniSearch: {
      searchOptions: {
        fuzzy: 0.2,        // Fuzzy matching tolerance
        prefix: true,      // Enable prefix search
        boost: {
          title: 4,        // Boost title matches
          heading: 3,      // Boost heading matches
          text: 2          // Boost text matches
        }
      }
    }
  }
}
```

## Search Tips

### Best Practices

1. **Use specific terms**: "array functions" > "array"
2. **Try different phrasings**: "loop" vs "iteration" vs "foreach"
3. **Search chapter numbers**: "Chapter 05" or just "05"
4. **Use technical terms**: "PDO", "OOP", "namespace"

### Examples

| Search Query | What It Finds |
|--------------|---------------|
| `array` | All mentions of arrays across chapters |
| `func` | Functions, functional programming, etc. |
| `05` | Chapter 05 and related content |
| `database PDO` | Database chapters with PDO content |
| `class oop` | Object-oriented programming chapters |
| `regex` | Regular expression content |

## What Gets Indexed

✅ **Indexed:**
- Page titles (H1)
- Section headings (H2, H3)
- Paragraph text
- Code comments (in markdown)
- List items
- Callout/tip boxes

❌ **Not Indexed:**
- Code blocks (syntax highlighted code)
- Image alt text
- Navigation menus
- Footer content

## Performance

- **Build time**: ~5-10 seconds for full site indexing
- **Search speed**: < 50ms for most queries
- **Index size**: ~200-300 KB (gzipped)
- **Memory usage**: Minimal (client-side only)

## Accessibility

The search modal is fully accessible:
- Keyboard navigation support
- Screen reader friendly
- ARIA labels and roles
- Focus management
- High contrast support

## Advanced Filtering

While not built-in, you can simulate filtering by adding series names to queries:

| Query | Effect |
|-------|--------|
| `array php basics` | Arrays in PHP Basics series |
| `ml ai-ml` | Machine learning in AI/ML series |
| `laravel python` | Laravel content in Python series |

## Troubleshooting

### Search not working
1. Rebuild the site: `npm run build`
2. Clear browser cache
3. Check JavaScript console for errors

### Missing results
1. Verify content is in markdown files (not images/PDFs)
2. Check if content is in excluded directories
3. Ensure proper heading structure (H1, H2, H3)

### Slow search
1. Reduce fuzzy matching: `fuzzy: 0.1`
2. Disable prefix search: `prefix: false`
3. Check browser extensions (ad blockers)

## Comparison with Alternatives

| Feature | Local Search | Algolia | Simple Search |
|---------|--------------|---------|---------------|
| Setup | ✅ Zero config | ❌ API keys | ✅ Simple |
| Cost | ✅ Free | ❌ Paid tier | ✅ Free |
| Speed | ✅ Instant | ⚠️ Network | ⚠️ Slower |
| Offline | ✅ Yes | ❌ No | ❌ No |
| Typo tolerance | ✅ Yes | ✅ Yes | ❌ No |

## Future Enhancements

Potential improvements:
- [ ] Custom filters (by series, level, topic)
- [ ] Search history
- [ ] Suggested queries
- [ ] Search analytics
- [ ] Context-aware results (based on current page)
- [ ] Multi-language support

## Resources

- [VitePress Local Search Docs](https://vitepress.dev/reference/default-theme-search#local-search)
- [MiniSearch (underlying library)](https://lucaong.github.io/minisearch/)
- [Search Best Practices](https://vitepress.dev/guide/theme-search)

