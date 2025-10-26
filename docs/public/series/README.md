# Code Examples

This directory contains all code examples from the tutorial series, served as static files so readers can view and download them directly.

## Structure

- `php-basics/code/` - Code examples for the PHP Basics series
- `ai-ml-php-developers/code/` - Code examples for the AI/ML for PHP Developers series

## Important Notes

1. **Source of Truth**: The original code files are maintained in `docs/series/*/code/` directories alongside the chapter content.

2. **Deployment**: These files are copied to `docs/public/series/*/code/` so VitePress can serve them as static assets.

3. **Updating Code**: When updating code examples:
   - Edit the files in `docs/series/*/code/` (next to the chapters)
   - Copy updated files to `docs/public/series/*/code/` before deploying
   - Or use the provided sync script: `npm run sync-code`

4. **URLs**: Code files are accessible at:
   - `/series/php-basics/code/...`
   - `/series/ai-ml-php-developers/code/...`

## Syncing Code Files

To sync code files from source to public directory:

```bash
# Sync all code files
cp -r docs/series/php-basics/code/* docs/public/series/php-basics/code/
cp -r docs/series/ai-ml-php-developers/code/* docs/public/series/ai-ml-php-developers/code/
```

This ensures the web-served versions match the source files.
