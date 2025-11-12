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
3. **[Python Developers ♥ PHP & Laravel](https://codewithphp.com/series/python-developers-love-php-laravel/)** — Transition from Python to PHP/Laravel

## 🚀 Quick Start

### Documentation Site

```bash
# Install dependencies
npm install

# Start development server (http://localhost:5173)
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
├── docs/                    # VitePress documentation site
│   ├── .vitepress/
│   │   ├── config.ts       # Site configuration & navigation
│   │   └── theme/          # Custom Vue components & styling
│   ├── index.md            # Landing page
│   └── series/             # Tutorial series content
│       ├── php-basics/
│       ├── ai-ml-php-developers/
│       └── python-developers-love-php-laravel/
│
├── code/                    # Executable code samples (organized by series)
│   ├── php-basics/
│   ├── ai-ml-php-developers/
│   └── python-developers-love-php-laravel/
│
├── testing/                 # Test infrastructure for code validation
│   ├── test-all-samples.php
│   └── TEST-SUMMARY-REPORT.md
│
├── imagen/                  # AI image generation (MCP server)
│   ├── src/               # Gemini 2.5 Flash integration
│   └── output/            # Generated hero images
│
└── scripts/                # Utility scripts
    ├── generate-social-images.js
    └── update-code-references.js
```

## 🛠️ Tech Stack

### Documentation Site

- **VitePress 1.6.4** — Modern static site generator
- **Vue 3** — Custom components (progress tracking, search, etc.)
- **TypeScript** — Type-safe configuration
- **GitHub Pages** — Hosting & deployment

> **Note:** Yes, I realize the irony of using VitePress (Vue.js) for a PHP site. However, it provides the best developer experience for technical documentation and works seamlessly with GitHub Pages.

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
- [vitepress-usage.mdc](.cursor/rules/vitepress-usage.mdc) — VitePress configuration

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
- **VitePress:** [vitepress.dev](https://vitepress.dev)
- **PHP Documentation:** [php.net](https://www.php.net)

## 📊 Project Status

- **PHP Basics Series:** ✅ Complete (25 chapters)
- **AI/ML Series:** ✅ Complete (25 chapters)
- **Python → PHP Series:** 🚧 In Progress (11 chapters)
- **Code Test Coverage:** 80.7% passing (96/119 samples)
- **Active Development:** ✅ Ongoing

## 📄 License

MIT License — See [LICENSE](LICENSE) for details.

---

**Built with ❤️ by [Dale Hurley](https://dalehurley.com)**
