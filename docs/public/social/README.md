# Social Share Images

This directory contains pre-generated social share images (1200×630px) for Open Graph and Twitter Cards.

## Generation

Images are **generated locally** during article creation and committed to git:

```bash
# Generate images for all chapters
node scripts/generate-social-images.js
```

## Naming Convention

```
{series}-chapter-{nn}.jpg
```

Examples:

- `php-basics-chapter-01.jpg`
- `ai-ml-php-developers-chapter-13.jpg`
- `php-basics-overview.jpg` (series index)
- `homepage.jpg` (site homepage)

## When to Regenerate

Regenerate images when:

- ✏️ Creating a new chapter
- ✏️ Updating a chapter title
- ✏️ Adding a new series
- 🎨 Changing color schemes in generator script

## Workflow

1. **Write/edit chapter** with proper frontmatter (title, series, chapter)
2. **Generate images**: `node scripts/generate-social-images.js`
3. **Review output**: Check `docs/public/social/` for new/updated images
4. **Commit together**:
   ```bash
   git add docs/series/{series}/chapters/{chapter}.md
   git add docs/public/social/{series}-chapter-{nn}.jpg
   git commit -m "Add Chapter XX with social image"
   ```

## Do NOT

- ❌ Delete this directory
- ❌ Regenerate images on every deploy (they're static assets)
- ❌ Edit images manually (regenerate from script instead)
- ❌ Commit without reviewing (check for quality)

## Customization

To customize image appearance, edit `scripts/generate-social-images.js`:

- **Colors**: Modify `SERIES_COLORS` object
- **Layout**: Edit `generateSVG()` function
- **Typography**: Adjust font sizes and positions

After customization, regenerate all images:

```bash
node scripts/generate-social-images.js
```

## Image Specifications

- **Dimensions**: 1200×630px (Open Graph standard)
- **Format**: JPEG (quality: 90)
- **File size**: Typically 50-200KB per image
- **Series colors**:
  - PHP Basics: Purple-blue gradient (#7C7EAF, #4F5887)
  - AI/ML Series: Blue gradient (#4A90E2, #2E5C8A)

## Verification

After generating, verify images work:

1. **Check file exists**: `ls docs/public/social/{series}-chapter-{nn}.jpg`
2. **Build site**: `npm run docs:build`
3. **Test sharing**: Use Facebook Debugger or Twitter Card Validator
4. **Validate locally**: Open image in browser to verify appearance

## Current Status

Generated images: **53** (as of last generation)

- Homepage: 1
- Series overviews: 2
- PHP Basics chapters: 24
- AI/ML chapters: 26

---

**Script Location**: `scripts/generate-social-images.js`  
**Documentation**: See `SEO-IMPLEMENTATION.md` for complete details
