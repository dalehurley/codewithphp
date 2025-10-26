# Deployment Checklist

## Pre-Deployment Steps

### 1. Sync Code Files
Before building or deploying, ensure code files are synced:

```bash
npm run sync-code
```

This copies all code examples from `docs/series/*/code/` to `docs/public/series/*/code/` so VitePress serves them.

### 2. Build the Site
```bash
npm run build
```

### 3. Verify Build Output
Check that code files are in the build output:

```bash
ls docs/.vitepress/dist/series/ai-ml-php-developers/code/chapter-01/
ls docs/.vitepress/dist/series/php-basics/code/
```

You should see all `.php`, `.csv`, `.db`, and other code files.

## Post-Deployment Verification

### Test Code File URLs

After deployment, verify these URLs are accessible:

**AI/ML Series:**
- https://codewithphp.com/series/ai-ml-php-developers/code/chapter-01/quick-start-demo.php
- https://codewithphp.com/series/ai-ml-php-developers/code/chapter-01/supervised-example.php

**PHP Basics Series:**
- https://codewithphp.com/series/php-basics/code/00-setup/phpinfo-test.php
- https://codewithphp.com/series/php-basics/code/01-first-script/hello-world.php

### Test Chapter Links

Open a chapter and click code file links to ensure they work:
- [Chapter 01: Introduction to AI/ML](https://codewithphp.com/series/ai-ml-php-developers/chapters/01-introduction-to-ai-and-machine-learning-for-php-developers)

## Common Issues

### Code files return 404

**Cause**: Code files not synced before build

**Fix**:
```bash
npm run sync-code
npm run build
# Re-deploy
```

### New chapter code not appearing

**Cause**: New code directory not created in public folder

**Fix**:
1. Create directory: `mkdir -p docs/public/series/[series]/code/[chapter]`
2. Run sync: `npm run sync-code`
3. Rebuild: `npm run build`

## Automated Deployment

If using GitHub Actions or CI/CD, ensure the workflow includes:

```yaml
- name: Sync code files
  run: npm run sync-code

- name: Build VitePress site
  run: npm run build
```

This ensures code files are always included in deployments.
