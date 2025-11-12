# Visual Styles System

The imagen tool now includes a comprehensive visual styles system that provides rich style vocabulary for diverse artistic expressions.

## Overview

The styles system is implemented in `src/styles.js` and integrates seamlessly with the prompt generator and MCP server. It supports:

- **40+ predefined styles** across 6 categories
- **Intelligent style resolution** (case-insensitive, flexible matching)
- **Style enhancement** with keywords and descriptions
- **Custom style support** for maximum flexibility

## Style Categories

### 🎨 Artistic & Aesthetic Styles (8 styles)
- realistic, impressionist, abstract, surreal, minimalist, popArt, cubist, graffiti

### 🧸 Cute, Fun, and Playful Themes (6 styles)
- kawaii, chibi, cartoon, comicBook, manga, whimsical

### 🧠 Conceptual & Mood-Based Themes (7 styles)
- cyberpunk, steampunk, fantasy, sciFi, noir, romantic, gothic

### 🌍 Cultural & Design-Influenced Themes (7 styles)
- ukiyoe, boho, retro, artDeco, psychedelic, propaganda, pinUp

### 📸 Photography Styles (5 styles)
- portrait, landscape, street, macro, cinematic

### 🔧 Technical & Diagram Styles (4 styles)
- diagram, infographic, blueprint, isometric

## Usage

### In MCP Server

```json
{
  "name": "generate_image",
  "arguments": {
    "prompt": "PHP developer coding",
    "style": "cyberpunk",
    "series": "php-basics",
    "chapter": "01",
    "slug": "hero"
  }
}
```

### In CLI

```bash
node src/cli.js generate "PHP developer coding" \
  --style cyberpunk \
  --series php-basics \
  --chapter 01 \
  --slug hero
```

### In Code

```javascript
import { getStyle, buildStylePrompt } from './styles.js';

// Get style object
const style = getStyle('cyberpunk');

// Build style prompt
const stylePrompt = buildStylePrompt('cyberpunk', {
  includeKeywords: true,
  includeDescription: true
});
```

## Style Resolution

The system uses flexible matching:

- **Exact match**: `cyberpunk` → finds `cyberpunk` style
- **Case-insensitive**: `CYBERPUNK` → finds `cyberpunk` style
- **Partial match**: `cyber` → finds `cyberpunk` style
- **Name match**: `Cyberpunk` → finds `cyberpunk` style
- **Custom fallback**: Unknown styles are used as-is

## Integration Points

### Prompt Generator (`src/prompt-generator.js`)

- **Creative mode**: Styles enhance the meta-prompt with comprehensive descriptions
- **Simple mode**: Styles are resolved and added to prompts with keywords

### MCP Server (`src/mcp-server.js`)

- **Style parameter**: Enhanced with full list of available styles
- **Color palette generation**: Uses style keywords to infer appropriate palettes
- **Theme matching**: Resolves styles from theme names

## API Reference

### `getStyle(styleName)`
Returns style object or null if not found.

### `buildStylePrompt(styleName, options)`
Builds comprehensive style prompt with description and keywords.

### `getStyleDescription(style)`
Returns formatted style description string.

### `getStyleKeywords(style)`
Returns array of style keywords.

### `listAllStyles()`
Returns all styles organized by category.

### `searchStyles(keyword)`
Searches styles by keyword across names, descriptions, and keywords.

## Examples

### Using Predefined Style

```javascript
import { buildStylePrompt } from './styles.js';

const prompt = buildStylePrompt('cyberpunk');
// Returns: "Cyberpunk style: Neon lights, dystopian tech cityscapes. Key characteristics: cyberpunk, neon lights, dystopian"
```

### Custom Style Description

```javascript
// Custom styles work too
const prompt = generateSimplePrompt("A robot", {
  style: "futuristic robot with chrome finish"
});
```

### Style in Creative Mode

```javascript
const prompt = await generatePrompt({
  prompt: "Building APIs",
  creative: true,
  title: "Building Your First API",
  style: "steampunk" // Will enhance the creative prompt
});
```

## Benefits

1. **Consistency**: Standardized style names across all tools
2. **Rich Descriptions**: Each style includes detailed descriptions and keywords
3. **Flexibility**: Supports both predefined and custom styles
4. **Intelligent Matching**: Flexible resolution handles variations
5. **Enhanced Prompts**: Styles automatically enhance image generation prompts

## Future Enhancements

Potential additions:
- Style combinations (e.g., "cyberpunk + minimalist")
- Style presets for common use cases
- Style preview generation
- Style recommendation based on content

