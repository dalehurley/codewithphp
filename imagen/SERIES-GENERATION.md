# Series Image Generation with Consistent Themes

The MCP server now supports generating a series of images with a consistent visual theme across multiple chapters. This ensures visual coherence while allowing each chapter to have unique content.

## New Tool: `generate_image_series`

### Overview

The `generate_image_series` tool generates multiple hero images for a series of chapters while maintaining visual consistency in:
- **Character**: Same character across all images (optional)
- **Color Palette**: Consistent color scheme throughout (optional)
- **Artistic Style**: Same visual style (comic book, vintage poster, etc.)

### Usage Example

```javascript
{
  "series": "python-developers-love-php-laravel",
  "theme": "comic book superhero",
  "chapters": [
    {
      "chapter": "00",
      "slug": "introduction-why-look-at-php-laravel-hero",
      "title": "Introduction: Why Look at PHP & Laravel",
      "description": "Discover why modern PHP and Laravel deserve your attention as a Python developer"
    },
    {
      "chapter": "01",
      "slug": "mapping-concepts-python-web-frameworks-vs-laravel-hero",
      "title": "Mapping Concepts: Python Web Frameworks vs Laravel",
      "description": "See how Django and Flask concepts map directly to Laravel"
    }
    // ... more chapters
  ],
  "consistencyOptions": {
    "maintainCharacter": true,
    "maintainColorPalette": true,
    "maintainStyle": true,
    "characterDescription": "A Python developer superhero with a cape",
    "colorPalette": "vibrant primary colors (red, blue, yellow), bold black outlines",
    "styleDescription": "comic book superhero style with bold outlines and action lines"
  },
  "creative_mode": true,
  "sizes": ["full", "thumbnail"]
}
```

### Parameters

#### Required

- **`series`**: One of `php-basics`, `ai-ml-php-developers`, `python-developers-love-php-laravel`
- **`theme`**: Theme description (e.g., "comic book superhero", "vintage poster", "modern illustration")
- **`chapters`**: Array of chapter objects, each with:
  - `chapter`: Chapter number (e.g., "00", "01", "15b")
  - `slug`: Image identifier slug (e.g., "hero", "introduction-hero")
  - `title`: Chapter title
  - `description`: (optional) Chapter description
  - `prompt`: (optional) Custom prompt to override theme-based generation

#### Optional

- **`consistencyOptions`**: Object controlling visual consistency
  - `maintainCharacter`: Use same character (default: `true`)
  - `maintainColorPalette`: Use same colors (default: `true`)
  - `maintainStyle`: Use same style (default: `true`)
  - `characterDescription`: Specific character to use consistently
  - `colorPalette`: Specific color palette to use consistently
  - `styleDescription`: Specific style description to use consistently

- **`creative_mode`**: Use creative meta-prompt generator (default: `true`)
- **`sizes`**: Array of sizes to generate (default: `["full", "thumbnail"]`)

### Theme-Based Color Palettes

The tool automatically selects color palettes based on common themes:

- **"comic book superhero"**: Vibrant primary colors (red, blue, yellow), bold black outlines
- **"vintage poster"**: Warm earth tones (orange, brown, cream), muted secondary colors
- **"modern illustration"**: Saturated colors with high contrast, clean gradients
- **"retro futuristic"**: Neon colors (cyan, magenta, electric blue), dark backgrounds
- **"minimalist"**: Monochrome with single accent color, clean whites

### Response Format

```json
{
  "success": true,
  "theme": "comic book superhero",
  "consistencyOptions": {
    "maintainCharacter": true,
    "maintainColorPalette": true,
    "maintainStyle": true,
    "characterDescription": "A comic book superhero character",
    "colorPalette": "vibrant primary colors...",
    "styleDescription": "comic book superhero"
  },
  "generatedCount": 11,
  "chapters": [
    {
      "chapter": "00",
      "title": "Introduction: Why Look at PHP & Laravel",
      "files": [
        {
          "size": "full",
          "path": "/images/python-developers-love-php-laravel/chapter-00-introduction-why-look-at-php-laravel-hero-full.webp",
          "absolutePath": "/path/to/file",
          "width": 1536,
          "height": 1024,
          "bytes": 410890,
          "formatted": "401.26 KB"
        }
      ],
      "markdown": "![Introduction: Why Look at PHP & Laravel](/images/...)"
    }
  ],
  "totalFiles": 22,
  "message": "Successfully generated 11 images with consistent theme: comic book superhero"
}
```

## Benefits

1. **Visual Consistency**: All images share the same character, colors, and style
2. **Efficiency**: Generate all images in one call instead of multiple individual calls
3. **Theme Coherence**: Ensures the series looks cohesive when viewed together
4. **Flexibility**: Can override individual chapters with custom prompts if needed

## Best Practices

1. **Define Theme Clearly**: Use descriptive theme names that include style keywords
2. **Provide Character Description**: For best consistency, provide a specific character description
3. **Specify Color Palette**: For exact color matching, provide the exact palette you want
4. **Use Creative Mode**: Enable `creative_mode` for more artistic, varied results
5. **Test with Few Chapters First**: Generate 2-3 chapters first to verify the theme works

## Example: Comic Book Superhero Theme

```javascript
{
  "series": "python-developers-love-php-laravel",
  "theme": "comic book superhero",
  "chapters": [
    // ... all 11 chapters
  ],
  "consistencyOptions": {
    "maintainCharacter": true,
    "maintainColorPalette": true,
    "maintainStyle": true,
    "characterDescription": "A Python developer superhero with a Python-themed cape, discovering PHP and Laravel powers",
    "colorPalette": "vibrant primary colors (red, blue, yellow), bold black outlines, bright highlights, action lines",
    "styleDescription": "comic book superhero style with bold outlines, dynamic poses, energy effects, and dramatic lighting"
  },
  "creative_mode": true
}
```

This will generate all hero images with:
- The same Python developer superhero character
- Consistent vibrant comic book colors
- Same comic book artistic style
- Each image showing different aspects of the learning journey

