# Code Files Setup for VitePress

## Problem

VitePress is a static site generator that only serves:
1. Markdown files (converted to HTML)
2. Files in the `docs/public/` directory (served as static assets)

Code example files (`.php`, `.csv`, etc.) in `docs/series/*/code/` directories were not being served, resulting in 404 errors when users clicked on code file links.

## Solution

Code files are now copied to `docs/public/series/*/code/` so VitePress serves them as static assets while maintaining the original files in their colocated positions next to the chapter content.

## Directory Structure

```
docs/
├── series/
│   ├── php-basics/
│   │   ├── chapters/          # Chapter markdown files
│   │   └── code/              # Source code files (for local dev)
│   └── ai-ml-php-developers/
│       ├── chapters/          # Chapter markdown files
│       └── code/              # Source code files (for local dev)
└── public/
    └── series/
        ├── php-basics/
        │   └── code/          # Copied code files (served by VitePress)
        └── ai-ml-php-developers/
            └── code/          # Copied code files (served by VitePress)
```

## Link Format

All chapter files now use absolute paths to code files:

**Before (404 errors):**
```markdown
[`quick-start-demo.php`](../code/chapter-01/quick-start-demo.php)
```

**After (works correctly):**
```markdown
[`quick-start-demo.php`](/series/ai-ml-php-developers/code/chapter-01/quick-start-demo.php)
```

## URLs

Code files are accessible at:
- https://codewithphp.com/series/php-basics/code/...
- https://codewithphp.com/series/ai-ml-php-developers/code/...

## Maintaining Code Files

### Updating Code Examples

1. **Edit** the source files in `docs/series/*/code/` (colocated with chapters)
2. **Sync** to public directory:
   ```bash
   npm run sync-code
   ```
3. **Commit** both the source and public versions

### Automated Sync Script

The `sync-code` npm script copies all code files:

```bash
npm run sync-code
```

This ensures web-served versions match source files.

## Configuration Changes

### VitePress Config (`docs/.vitepress/config.ts`)

Removed unnecessary `ignoreDeadLinks` patterns:
- Removed: `/\.\.\/code\//` (relative code links)
- Removed: `/^\/series\/php-basics\/code\//` (absolute code links)

These are no longer needed because code files are properly served from the public directory.

## Benefits

1. **Code files are accessible**: Users can view/download examples directly
2. **Colocated content**: Original code files stay next to chapter content for easy editing
3. **Version controlled**: Both source and public versions are tracked in git
4. **Simple workflow**: `npm run sync-code` keeps everything in sync

## Troubleshooting

### 404 on code file link

**Symptoms**: Clicking a code file link returns 404

**Solutions**:
1. Verify file exists in `docs/public/series/*/code/`
2. Run `npm run sync-code` to copy files
3. Check link uses absolute path: `/series/.../code/...`

### Code changes not reflecting on site

**Cause**: Public directory not synced after editing source files

**Solution**: 
```bash
npm run sync-code
npm run build
```

### New chapter code not appearing

**Cause**: New code directory not created in public folder

**Solution**:
1. Create directory structure in `docs/public/series/[series-name]/code/`
2. Run `npm run sync-code`
3. Or manually copy: `cp -r docs/series/.../code/... docs/public/series/.../code/...`

## Future Improvements

Consider automating code sync in the build process:
- Add pre-build hook to sync code files
- Or use VitePress plugin to copy code directories automatically
- Or use symlinks (if supported by deployment platform)
