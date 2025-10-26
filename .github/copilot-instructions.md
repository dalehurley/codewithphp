# Copilot AI Agent Instructions for PHP-From-Scratch

## Project Overview

- **Purpose:** This repo is a comprehensive, example-driven PHP learning series. It uses VitePress for documentation and organizes code samples by chapter.
- **Structure:**
  - `docs/` — VitePress content root (site config, landing, series, chapters, code)
  - `docs/series/<series>/chapters/` — Numbered markdown chapters
  - `docs/series/<series>/code/` — Code samples for each chapter, colocated
  - `.github/workflows/` — GitHub Actions for VitePress deploy
  - `.cursor/rules/` — Project structure and navigation rules
  - `package.json` — VitePress scripts (dev/build/preview)

## Key Conventions & Patterns

- **Each series is self-contained:** All content, code, and navigation for a series lives under its own folder in `docs/series/`.
- **Code samples:** Each chapter has matching code in `code/`, named by chapter and topic. Exercise solutions are in `solutions/` subfolders.
- **Markdown-first:** All learning content is authored in markdown, with code blocks and explanations inline.
- **PSR-12/PSR-4:** Code samples follow modern PHP standards (see Chapter 16 for details).
- **No framework dependencies** unless in framework-specific chapters (e.g., Laravel, Symfony).
- **Composer:** Used only in relevant chapters; not required for the whole repo.

## Developer Workflows

- **Preview docs locally:**
  ```bash
  npm install
  npm run docs:dev
  # Visit http://localhost:5173
  ```
- **Build for production:**
  ```bash
  npm run docs:build
  ```
- **Deploy:**
  # Copilot AI Agent Instructions for PHP-From-Scratch

  ## Project Architecture & Structure

  - **Purpose:** Example-driven PHP learning series, organized by topic and chapter, with all code and docs in `docs/`.
  - **VitePress** powers the documentation site. All config is in `docs/.vitepress/config.ts`.
  - **Series are self-contained:** Each under `docs/series/<series>/` with `chapters/` (markdown) and `code/` (PHP samples, exercises, solutions).
  - **No global Composer dependencies:** Composer is only used in chapters that teach it (see `12-dependency-management-with-composer.md`).
  - **No framework dependencies** except in framework chapters (e.g., Laravel, Symfony).
  - **All code targets PHP 8.4** (use new features, e.g., property hooks, asymmetric visibility, etc.).

  ## Developer Workflows

  - **Preview docs locally:**
    ```bash
    npm install
    npm run docs:dev
    # Visit http://localhost:5173
    ```
  - **Build for production:**
    ```bash
    npm run docs:build
    ```
  - **Deploy:**
    - GitHub Actions auto-deploys `main` to Pages via `.github/workflows/deploy.yml`.
  - **Test code samples:**
    - Run PHP scripts directly: `php <filename>.php` (from `code/` folders)
    - Some chapters include test scripts or expected output in comments/README.
  - **Debugging:**
    - Use VS Code with Xdebug (see `00-setup/README.md`).

  ## Project Conventions & Patterns

  - **Chapters:** Markdown files with required frontmatter (`title`, `description`, `series`, `chapter`, `order`, `difficulty`, `prerequisites`).
  - **Naming:** Chapters are zero-padded and slugged, e.g., `01-your-first-php-script.md`.
  - **Code samples:** Placed in `code/` subfolders, named by topic. Exercise solutions in `solutions/`.
  - **Navigation:** Update `docs/.vitepress/config.ts` for new/removed chapters.
  - **Markdown-first:** All learning content is in markdown, with code blocks and explanations inline. Large code in `code/` and linked relatively.
  - **PSR-12/PSR-4:** All code samples follow modern PHP standards (see `16-writing-better-code-with-psr-1-and-psr-12.md`).
  - **Links:** Use absolute-from-root for docs, relative for code. External links must be descriptive.

  ## Integration Points

  - **VitePress:** All config in `docs/.vitepress/config.ts`.
  - **GitHub Pages:** Deploy via Actions; see `.github/workflows/`.
  - **Composer:** Only in relevant chapters, never global.

  ## Examples

  - Add a chapter: `docs/series/php-basics/chapters/NN-title.md` + matching code in `code/`.
  - Add a code sample: Place in relevant `code/` subfolder, named by topic.
  - Update navigation: Edit `docs/.vitepress/config.ts`.

  ## Key Files & Directories

  - `docs/series/` — All content, code, and navigation for each series.
  - `docs/.vitepress/config.ts` — Site config and sidebar/nav structure.
  - `.github/workflows/deploy.yml` — GitHub Pages deploy workflow.
  - `README.md`, `.cursor/rules/`, and `00-setup/README.md` — Project rules, setup, and troubleshooting.

  ## AI Agent Guidance

  - **Do not restructure `docs/series/` folders.**
  - **Do not add global Composer dependencies.**
  - **Follow naming/placement conventions for new chapters and code.**
  - **Keep code and explanations tightly coupled in markdown.**
  - **Reference key files above for patterns and rules.**

  For more, see `.cursor/rules/project-structure.mdc` and `README.md`.
- Validate after each major step with exact commands and expected outputs.
- Include at least three likely errors and fixes.
- Optimise for copy-paste and cross-platform (macOS + Linux; add Windows notes if needed).
- Prefer links to official docs using descriptive text.

### Snippet Conventions

- Code fences include language and filename comments when helpful.

```bash
# filename: scripts/clean-slate.sh
# Reset project (Warning: destructive)
# Tip: run with --dry-run first if supported
git clean -xfd
git reset --hard
```

```diff
# Example diff snippet
@@
- "dev": "vite --open",
 + "dev": "vite"
```
