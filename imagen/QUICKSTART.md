# Quick Start Guide

Get up and running with Imagen in 5 minutes.

## 1. Setup (2 min)

```bash
# Navigate to imagen directory
cd imagen

# Install dependencies (already done if you see node_modules/)
npm install

# Create .env file
echo "GEMINI_API_KEY=your_api_key_here" > .env
```

**Get your API key:** https://ai.google.dev/gemini-api/docs/api-key

## 2. Test Connection (30 sec)

```bash
node src/cli.js test
```

Expected output:

```
🧪 Testing Gemini API connection...
Generating test image...
✅ API connection successful!
Generated image: 123 KB
```

## 3. Generate Your First Image (2 min)

### Simple Image

```bash
node src/cli.js generate "Modern PHP code editor with dark theme" \
  --series php-basics \
  --chapter 01 \
  --slug editor \
  --sizes full,thumbnail
```

### Creative 1950s Poster

```bash
node src/cli.js generate "Building REST APIs" \
  --creative \
  --title "Building Your First REST API" \
  --series php-basics \
  --chapter 23 \
  --slug api-hero \
  --sizes full,thumbnail
```

## 4. View Your Images

Images are saved to: `/docs/public/images/{series}/`

Open in browser:

```bash
# Start VitePress dev server from project root
cd ..
npm run dev

# Visit: http://localhost:5173/images/php-basics/chapter-01-editor-full.webp
```

Or view directly:

```bash
open docs/public/images/php-basics/chapter-01-editor-full.webp
```

## 5. Use in VitePress Markdown

```markdown
![Editor Setup](/images/php-basics/chapter-01-editor-full.webp)
```

## MCP Integration (Optional)

To use with Claude Desktop:

1. **Edit Claude config:**

   ```bash
   # macOS
   open ~/Library/Application\ Support/Claude/claude_desktop_config.json

   # Add:
   ```

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

2. **Restart Claude Desktop**

3. **Test in Claude:**
   ```
   Generate an image for a PHP tutorial about arrays using the generate_image tool.
   Series: php-basics, Chapter: 06, Slug: hero
   ```

## Common Issues

### "GEMINI_API_KEY is required"

→ Create `.env` file with your API key

### "No matching version found"

→ Run `npm install` again

### Images not showing in VitePress

→ Ensure path is `/images/{series}/filename.webp` (not `/docs/public/images/...`)

### MCP server not in Claude

→ Use absolute paths in config, restart Claude

## Next Steps

- Read full [README.md](README.md) for all features
- Explore creative mode variations
- Generate multiple sizes
- Try batch generation

## Examples Gallery

Generate a set of examples:

```bash
# Technical diagram
node src/cli.js generate "PHP request lifecycle flowchart" \
  --series php-basics --chapter 17 --slug lifecycle \
  --style "clean technical diagram"

# Screenshot style
node src/cli.js generate "VS Code with PHP extensions" \
  --series php-basics --chapter 00 --slug vscode \
  --style "realistic screenshot"

# Creative hero
node src/cli.js generate "Mastering Arrays" \
  --creative --series php-basics --chapter 06 --slug hero

# Concept illustration
node src/cli.js generate "Object-oriented programming concepts" \
  --series php-basics --chapter 08 --slug oop-concepts \
  --style "modern flat illustration"
```

Happy image generating! 🎨
