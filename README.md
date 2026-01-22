# Code with PHP

> **A comprehensive, open-source learning platform helping developers fall in love with PHP and its ecosystem.**

🌐 **Live Site:** [codewithphp.com](https://codewithphp.com)  
👤 **Author:** [Dale Hurley](https://dalehurley.com)  
📄 **License:** MIT

## 🎯 About

Code with PHP is a tutorial-based learning resource featuring hands-on, reproducible tutorials for modern PHP development. Every code sample is tested, every chapter is production-ready, and every tutorial is designed to teach real-world skills.

### Tutorial Series

1. **[PHP Basics](https://codewithphp.com/series/php-basics/)** — Master PHP fundamentals from zero to hero
2. **[AI/ML for PHP Developers](https://codewithphp.com/series/ai-ml-php-developers/)** — Build machine learning applications with PHP
3. **[Build a CRM with Laravel 12](https://codewithphp.com/series/build-crm-laravel-12/)** — Build a complete CRM system with Laravel 12
4. **[Claude for PHP Developers](https://codewithphp.com/series/claude-php-developers/)** — Integrate Claude AI into PHP applications
5. **[Data Science for PHP Developers](https://codewithphp.com/series/data-science-php-developers/)** — Data analysis and visualization with PHP
6. **[PHP Algorithms](https://codewithphp.com/series/php-algorithms/)** — Master algorithms and data structures in PHP
7. **[PHP for Java Developers](https://codewithphp.com/series/php-for-java-developers/)** — Transition from Java to PHP
8. **[PHP for TypeScript Developers](https://codewithphp.com/series/php-typescript-developers/)** — Transition from TypeScript to PHP
9. **[Python Developers ♥ PHP & Laravel](https://codewithphp.com/series/python-developers-love-php-laravel/)** — Transition from Python to PHP/Laravel
10. **[Ruby on Rails Developers ♥ Laravel](https://codewithphp.com/series/rails-developers-love-laravel/)** — Transition from Rails to Laravel

## 🚀 Quick Start

### Documentation Site

```bash
# Install dependencies
npm install

# Start development server (http://localhost:4321)
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Testing Code Samples

All tutorial code is validated in the `testing/` directory before publication.

```bash
cd testing

# Run all tests
php test-all-samples.php

# View test results
cat TEST-SUMMARY-REPORT.md
```

**Current Test Status:** 96/119 passing (80.7%) 🎉

## 📁 Project Structure

```
PHP-From-Scratch/
├── src/                     # Astro Starlight documentation site
│   ├── content/
│   │   ├── docs/           # Tutorial content
│   │   │   ├── index.mdx   # Landing page
│   │   │   └── series/     # Tutorial series content
│   │   │       ├── php-basics/
│   │   │       ├── ai-ml-php-developers/
│   │   │       ├── build-crm-laravel-12/
│   │   │       ├── claude-php-developers/
│   │   │       ├── data-science-php-developers/
│   │   │       ├── php-algorithms/
│   │   │       ├── php-for-java-developers/
│   │   │       ├── php-typescript-developers/
│   │   │       ├── python-developers-love-php-laravel/
│   │   │       └── rails-developers-love-laravel/
│   │   └── config.ts       # Content collections configuration
│   ├── styles/             # Custom CSS
│   └── Head.astro          # Custom head component
│
├── code/                    # Executable code samples (organized by series)
│   ├── php-basics/
│   ├── ai-ml-php-developers/
│   └── [other series]/
│
├── testing/                 # Test infrastructure for code validation
│   ├── test-all-samples.php
│   └── TEST-SUMMARY-REPORT.md
│
├── imagen/                  # AI image generation (MCP server)
│   ├── src/               # Gemini 2.5 Flash integration
│   └── output/            # Generated hero images
│
├── public/                  # Static assets
│   ├── images/            # Hero images and graphics
│   └── social/            # Social media preview images
│
├── scripts/                # Utility scripts
│   ├── generate-social-images.js
│   └── update-code-references.js
│
└── astro.config.mjs        # Astro & Starlight configuration
```

## 🛠️ Tech Stack

### Documentation Site

- **Astro 5** — Modern static site generator with component islands
- **Starlight** — Astro's documentation theme with built-in features
- **TypeScript** — Type-safe configuration
- **MDX** — Enhanced markdown with component support
- **GitHub Pages** — Hosting & deployment

### Image Generation

- **Google Gemini 2.5 Flash** — AI image generation
- **MCP (Model Context Protocol)** — Claude Desktop integration
- **Node.js** — Image processing pipeline

## 📝 Contributing

We welcome contributions! Here's how to get started:

1. **Content Contributions**

   - Edit pages via the "Edit this page" link on any chapter
   - Follow authoring guidelines in `.cursor/rules/`
   - All code samples must pass tests in `testing/`

2. **Code Quality Standards**

   - PHP 8.4+ compatible
   - PSR-12 coding standards
   - Complete, runnable examples (no partial code)
   - Proper error handling and type hints

3. **Testing Requirements**
   - Copy code to `testing/<series-name>/`
   - Run `php test-all-samples.php`
   - Document expected failures if applicable

### Authoring Guidelines

Key rules documents:

- [tutorials-global.mdc](.cursor/rules/tutorials-global.mdc) — Global writing standards
- [authoring-guidelines.mdc](.cursor/rules/authoring-guidelines.mdc) — Tutorial structure & patterns
- [astro.config.mjs](astro.config.mjs) — Astro & Starlight configuration

## 🎨 Features

### For Learners

- ✅ **Progress Tracking** — Built-in chapter completion tracking
- 🔍 **Full-Text Search** — Search across all tutorials
- 📱 **Mobile-Friendly** — Responsive design for learning on the go
- 🎯 **Hands-On Code** — Every example is tested and runnable
- 🔗 **Deep Linking** — Share specific sections with others

### For Contributors

- 🧪 **Automated Testing** — Validate all code samples before publish
- 🎨 **Image Generation** — AI-generated hero images for chapters
- 🔄 **Real-Time Preview** — See changes instantly

## 🔗 Links

- **Website:** [codewithphp.com](https://codewithphp.com)
- **Repository:** [github.com/dalehurley/codewithphp](https://github.com/dalehurley/codewithphp)
- **Author:** [dalehurley.com](https://dalehurley.com)
- **Astro:** [astro.build](https://astro.build)
- **Starlight:** [starlight.astro.build](https://starlight.astro.build)
- **PHP Documentation:** [php.net](https://www.php.net)

## 📊 Project Status

- **PHP Basics:** ✅ Complete (25 chapters)
- **AI/ML for PHP Developers:** ✅ Complete (25 chapters)
- **Build a CRM with Laravel 12:** 🚧 In Progress (40 chapters planned)
- **Claude for PHP Developers:** 🚧 In Progress (40 chapters planned)
- **Data Science for PHP Developers:** 🆕 Starting
- **PHP Algorithms:** 🚧 In Progress (38 chapters planned)
- **PHP for Java Developers:** 🚧 In Progress (23 chapters planned)
- **PHP for TypeScript Developers:** 🆕 Starting (16 chapters planned)
- **Python → PHP/Laravel:** 🚧 In Progress (11 chapters)
- **Rails → Laravel:** 🚧 In Progress (11 chapters)
- **Code Test Coverage:** 80.7% passing (96/119 samples)
- **Active Development:** ✅ Ongoing

## 📄 License

MIT License — See [LICENSE](LICENSE) for details.

---

**Built with ❤️ by [Dale Hurley](https://dalehurley.com)**
