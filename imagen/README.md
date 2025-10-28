# Imagen - Image Generation Tool with MCP Server

Generate stunning images using Google's Gemini 2.5 Flash Image API with support for both command-line usage and LLM integration via Model Context Protocol (MCP).

## Features

- 🎨 **Gemini 2.5 Flash Image API** - Latest image generation technology
- 🖼️ **Multiple sizes** - Generate full, medium, and thumbnail variants
- ⚡ **WebP optimization** - Automatic compression and format conversion
- 🎭 **Creative mode** - Unique 1950s-style poster art generator
- 🛠️ **CLI interface** - Direct command-line usage
- 🤖 **MCP server** - LLM tool integration for Claude and other AI assistants
- 📁 **VitePress-ready** - Output optimized for documentation sites

## Installation

```bash
cd imagen
npm install
```

## Configuration

Create a `.env` file in the `imagen/` directory:

```bash
GEMINI_API_KEY=your_api_key_here
```

Get your API key from: https://ai.google.dev/gemini-api/docs/api-key

## Usage

### CLI - Command Line Interface

#### Basic Image Generation

```bash
node src/cli.js generate "A modern PHP code editor with syntax highlighting" \
  --series php-basics \
  --chapter 01 \
  --slug editor-setup \
  --sizes full,thumbnail
```

#### Creative Mode (1950s Poster Style)

```bash
node src/cli.js generate "Building Your First PHP API" \
  --creative \
  --title "Building Your First PHP API" \
  --content "Learn how to create REST APIs with PHP..." \
  --series php-basics \
  --chapter 23 \
  --slug api-hero \
  --sizes full,thumbnail
```

#### Generate Multiple Variations

```bash
node src/cli.js generate "PHP developer debugging code" \
  --series php-basics \
  --chapter 11 \
  --slug error-handling \
  --count 4 \
  --sizes full,medium,thumbnail
```

#### Custom Output Path

```bash
node src/cli.js generate "Database diagram" \
  --output ./custom/path/database-diagram \
  --sizes full
```

#### Test API Connection

```bash
node src/cli.js test
```

### MCP Server - LLM Integration

The MCP server allows Claude (or other MCP-compatible LLMs) to generate images directly.

#### Start the Server

```bash
node src/mcp-server.js
```

#### Configure Claude Desktop

Add to your Claude Desktop config (`~/Library/Application Support/Claude/claude_desktop_config.json` on macOS):

```json
{
  "mcpServers": {
    "imagen": {
      "command": "node",
      "args": [
        "/Users/dalehurley/Code/PHP-From-Scratch/imagen/src/mcp-server.js"
      ],
      "env": {
        "GEMINI_API_KEY": "your_api_key_here"
      }
    }
  }
}
```

#### Tool Schema

When configured, LLMs can call the `generate_image` tool:

```json
{
  "name": "generate_image",
  "arguments": {
    "prompt": "Vintage 1950s poster showing PHP developer debugging code",
    "series": "php-basics",
    "chapter": "11",
    "slug": "error-handling-hero",
    "creative_mode": false,
    "sizes": ["full", "thumbnail"]
  }
}
```

**Parameters:**

- `prompt` (string, required) - Image generation prompt
- `series` (enum, required) - Tutorial series: `php-basics` or `ai-ml-php-developers`
- `chapter` (string, required) - Chapter number (e.g., "01", "15b")
- `slug` (string, required) - Image identifier (e.g., "hero", "diagram")
- `creative_mode` (boolean, default: false) - Use creative meta-prompt generator
- `title` (string, optional) - Blog post title (for creative mode)
- `content` (string, optional) - Blog post content (for creative mode)
- `style` (string, optional) - Style descriptor (e.g., "photorealistic", "illustration")
- `sizes` (array, default: ["full", "thumbnail"]) - Image sizes to generate
- `count` (number, default: 1, max: 4) - Number of variations

## Output Structure

Images are saved to: `/docs/public/images/{series}/`

**Filename pattern:** `chapter-{nn}-{slug}-{size}.webp`

**Examples:**

- `/docs/public/images/php-basics/chapter-01-hero-full.webp`
- `/docs/public/images/php-basics/chapter-01-hero-thumbnail.webp`

**VitePress reference:**

```markdown
![hero](/images/php-basics/chapter-01-hero-full.webp)
```

## Image Sizes

- **full**: 1536×1024 (3:2 hero images)
- **medium**: 768×512 (3:2 medium preview)
- **thumbnail**: 384×256 (3:2 small preview)

All images are:

- Converted to WebP format
- Optimized for web (quality: 90)
- Responsive and retina-ready

## Creative Mode

Creative mode generates unique 1950s-style propaganda poster art with:

- Randomized characters, settings, and compositions
- Vintage aesthetic with halftone shading
- Brand-aligned visuals for Code with PHP
- Maximum variation to avoid repetitive designs

**When to use:**

- Hero images for tutorial chapters
- Feature graphics for blog posts
- Eye-catching promotional material

**When not to use:**

- Technical diagrams
- Code screenshots
- Literal illustrations of concepts

## Examples

### Example 1: Simple Diagram

```bash
node src/cli.js generate "Flowchart showing PHP request lifecycle" \
  --series php-basics \
  --chapter 17 \
  --slug request-lifecycle \
  --style "clean technical diagram" \
  --sizes full
```

### Example 2: Creative Hero Image

```bash
node src/cli.js generate \
  --creative \
  --title "Mastering String Manipulation" \
  --content "Learn powerful string functions in PHP" \
  --series php-basics \
  --chapter 07 \
  --slug hero \
  --sizes full,thumbnail
```

### Example 3: Screenshot-style Image

```bash
node src/cli.js generate "Modern code editor showing PHP namespaces" \
  --series php-basics \
  --chapter 10 \
  --slug editor-namespaces \
  --style "realistic screenshot" \
  --sizes full,medium
```

## Troubleshooting

### Error: "GEMINI_API_KEY is required"

**Solution:** Create `.env` file with your API key:

```bash
echo "GEMINI_API_KEY=your_key_here" > .env
```

### Error: "Image generation failed"

**Causes:**

- Invalid API key
- Rate limiting
- Network issues
- Invalid prompt (prohibited content)

**Solutions:**

- Verify API key is correct
- Wait a few seconds and retry
- Check internet connection
- Rephrase prompt if flagged by safety filters

### MCP Server Not Showing in Claude

**Solution:**

1. Verify config path: `~/Library/Application Support/Claude/claude_desktop_config.json`
2. Check absolute paths in config (use full paths, not relative)
3. Restart Claude Desktop completely
4. Check Claude logs for errors

### Images Not Displaying in VitePress

**Solution:**

- Ensure images are in `/docs/public/images/`
- Use correct markdown path: `/images/{series}/filename.webp`
- Run VitePress dev server: `npm run dev`
- Check browser console for 404 errors

## API Reference

### ImageGenerator

```javascript
import { ImageGenerator } from "./src/generator.js";

const generator = new ImageGenerator();

// Single image
const buffer = await generator.generateSingle("A robot");

// Multiple variations
const buffers = await generator.generateMultiple("A robot", 4);
```

### ImageProcessor

```javascript
import { ImageProcessor } from "./src/image-processor.js";

const processor = new ImageProcessor();

// Process and save
const results = await processor.processAndSave(imageBuffer, "/path/to/output", [
  "full",
  "thumbnail",
]);
```

### Prompt Generator

```javascript
import { generatePrompt } from "./src/prompt-generator.js";

// Simple prompt
const prompt = generatePrompt({
  prompt: "A red car",
  creative: false,
});

// Creative prompt
const creativePrompt = generatePrompt({
  prompt: "Building APIs",
  creative: true,
  title: "Building Your First API",
  content: "Learn REST API development...",
});
```

## Architecture

```
imagen/
├── src/
│   ├── generator.js         # Gemini API integration
│   ├── image-processor.js   # Sharp image processing
│   ├── prompt-generator.js  # Creative prompt system
│   ├── cli.js              # Command-line interface
│   └── mcp-server.js       # MCP server
├── config/
│   └── default.js          # Configuration
├── output/                 # Temporary storage
└── package.json
```

## Dependencies

- **@google/genai** - Official Gemini API client
- **@modelcontextprotocol/sdk** - MCP server implementation
- **sharp** - High-performance image processing
- **commander** - CLI framework

## License

MIT License - See LICENSE file for details

## Contributing

Contributions welcome! Please follow the Code with PHP coding standards.

## Support

For issues or questions:

- GitHub Issues: https://github.com/dalehurley/codewithphp/issues
- Documentation: https://codewithphp.com

## Roadmap

- [ ] Image editing capabilities (masks, variations)
- [ ] Batch generation from CSV
- [ ] Integration with VitePress frontmatter
- [ ] Image gallery viewer
- [ ] Custom brand presets
- [ ] A/B testing variants
