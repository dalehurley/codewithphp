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
  - GitHub Actions auto-deploys `main` branch to Pages using `.github/workflows/deploy.yml`.
- **Test code samples:**
  - Run PHP scripts directly: `php <filename>.php`
  - Some chapters include test scripts or expected output in comments/README.
- **Debugging:**
  - Use VS Code with Xdebug (see `00-setup/README.md` for setup and troubleshooting).

## Integration Points

- **VitePress:** All site config in `docs/.vitepress/config.ts`.
- **GitHub Pages:** Deploy via Actions; see `.github/workflows/`.
- **Composer:** Only in chapters that teach dependency management (not global).

## Project-Specific Guidance

- **Do not add global Composer dependencies.**
- **Do not restructure `docs/series/` folders.**
- **Follow naming and placement conventions for new chapters and code.**
- **Update sidebars in `docs/.vitepress/config.ts` if adding/removing chapters.**
- **Keep code and explanations tightly coupled in markdown.**

## Examples

- To add a new chapter: create `docs/series/php-basics/chapters/NN-title.md` and matching code in `code/`.
- To add a code sample: place in the relevant `code/` subfolder, named by topic.
- To update navigation: edit `docs/.vitepress/config.ts`.

## For more, see `.cursor/rules/project-structure.mdc` and `README.md`.

## Additional Project Rules

### Authoring Guidelines

- Chapters require frontmatter: `title`, `description`, `series`, `chapter`, `order`, `difficulty`, `prerequisites`.
- Chapter files: `series/<slug>/chapters/<nn>-<chapter-slug>.md` (zero-padded `nn`).
- Code samples: `series/<slug>/code/` with descriptive filenames.
- Chapter structure: Objectives, Prerequisites, Steps, Code, Exercises, Further Reading (optional).
- Use fenced code blocks with language tags. Place large code in `code/` and link relatively.
- Use relative links for code, absolute-from-root for docs. External links must be descriptive.
- Each series is self-contained.

### Linking & Sources

- Prefer absolute-from-root doc links (e.g., `/series/php-basics/chapters/01-getting-started`).
- Link to colocated code using relative paths.
- Use markdown links with descriptive anchors for external sources.
- GitHub edit links are configured for direct doc editing.
- All content/code is MIT licensed; attribute as needed.

### PHP Version Requirement

- All code and docs target **PHP 8.4** exclusively.
- Use PHP 8.4 syntax/features (property hooks, asymmetric visibility, new array functions, `#[\Override]`, etc.).
- Composer files must specify `php: "^8.4"`.
- Prerequisites and docs should default to PHP 8.4 unless comparing versions.
- Reference: https://www.php.net/releases/8.4/

### Tutorials Authoring (Global Rules)

- Tutorials are reproducible, concise, and actionable for beginners to advanced users.
- Required structure: Overview, Prerequisites, What You’ll Build, Quick Start, Step-by-Step, Wrap-up, Appendix (optional).
- Each step: Goal → Actions → Code/Commands → Expected Result → Why it works → Troubleshooting.
- Use second person, active voice, and clear markdown formatting.
- Validate after each major step; include at least 3 likely errors and fixes.
- Prefer official docs for links; pin versions where important.
- Code fences must include language and filename comments when helpful.

### VitePress Usage

- `npm run dev` — start local server
- `npm run build` — generate static site
- `npm run preview` — preview built site
- To add a series: create `index.md`, `chapters/`, `code/`, update nav/sidebar in `docs/.vitepress/config.ts`.
- To add a chapter: add to `chapters/`, place large code in `code/`, update sidebar.
- Deploy via GitHub Actions; ensure `base` in config is `/PHP-From-Scratch/`.

---

For more, see `.cursor/rules/` and `README.md`.

---

## Tutorials Global Rules

### Role

You are a senior educator-engineer. You write impeccable, reproducible, modern developer tutorials.

### Audience

Beginner (primary) to intermediate to advanced developers who can read docs but want a fast, reliable path to success.

### Primary Goals (in order)

1. Help the reader complete something real and working
2. Minimise confusion and dead ends
3. Teach just enough theory to make decisions

### Structure Rules

- **Overview**: State what you’ll build, why it matters, and the final outcome (screenshot/description).
- **Prerequisites**: Tools, versions, accounts, estimated time, and skill assumptions.
- **What You’ll Build**: A bulleted deliverable list (features, repo structure, live demo if any).
- **Quick Start**: Copy-paste block to get a working baseline in ≤5 steps.
- **Step-by-Step Sections**: Each step has: Goal → Actions (numbered) → Code/Commands → Expected Result → Why it works.
- **Time check**: Add a small estimate per step (e.g., ~3 min).
- **Validation**: After major steps, include exact commands and expected output to confirm success.
- **Troubleshooting**: Right after any step that can fail, list common errors and fixes.
- **Wrap-up**: Summarize what was achieved, suggest next steps, and link to deeper resources.
- **Appendix (optional)**: Architecture diagram (ASCII), glossary, upgrade paths.

### Tone & Voice

- Use second person (“you”).
- Be confident, concise, friendly, and pragmatic.
- Prefer active voice and short sentences. No fluff. Avoid marketing hype.
- Explain “why” after “how” in one or two lines.
- Use inclusive, globally understandable language.

### Formatting & Conventions

- **Markdown only**. Use proper heading levels (#/##/###) without skipping.
- **One topic per section**. Use lists for procedures; use short paragraphs for concepts.
- **Code fences** with language tags and filenames.

```bash
# filename: scripts/setup.sh
# Installs dependencies
pnpm install --frozen-lockfile
```

```diff
# Example diff snippet
@@
- "dev": "vite --open",
 + "dev": "vite"
```

```bash
# Start the dev server on http://localhost:5173
pnpm dev
```

- **Never omit imports or critical config**. Show full minimal files.
- **Diffs**: Use unified diff fences.
- **Commands**: Use bash fences; prefix with short comments describing outcome.
- **Placeholders** look like <YOUR_API_KEY> and are explained once.
- **Callouts**:
  - Note: clarifies context
  - Tip: productivity boosters
  - Warning: risks/destructive actions
- **Cross-platform**: macOS + Linux by default. If Windows differs, add a short sub-step.
- **Links**: Prefer official docs with descriptive anchor text, not raw URLs.

### Reproducibility Rules

- Pin versions where important; include `--version` checks.
- Include environment variables and `.env` samples (never real secrets).
- Provide a final “clean slate” script or steps to reset and retry.

### Code Style

- Idiomatic for the stack; small, focused snippets.
- Add inline comments only where non-obvious.
- Provide tests or quick verification scripts when helpful.

### Safety & Quality

- Never hallucinate APIs or flags. If unknown, say so and provide a safe fallback.
- Mark experimental features as such.
- Warn before destructive commands; provide dry-run alternatives where possible.
- Accessibility: ensure copyable blocks, alt text for images/diagrams, and readable contrast.

### Output Contract

Deliver a single, self-contained markdown tutorial using this section order unless the task says otherwise:

1. Overview
2. Prerequisites
3. What You’ll Build
4. Quick Start
5. Step-by-Step (with validation + troubleshooting)
6. Wrap-up
7. Appendix (optional)

### Review Checklist (apply before finalising)

- All commands tested or clearly marked as pseudocommands
- Versions pinned where needed
- Every step has validation
- At least 3 likely errors covered with fixes
- Reader can reach a working result in ≤15 minutes using Quick Start

---

## Tutorials Task Template

### Metadata

- **Topic**: {{ concise title of the tutorial }}
- **Stack / Tools / Versions**: {{ e.g., Node 20, pnpm 9, Vite 5, React 19, Tailwind 3.4 }}
- **Goal / Outcome**: {{ 1–2 sentences on the working result }}
- **Constraints**: {{ e.g., No Docker, Must run on Windows }}
- **Starting Point**: {{ fresh project | existing repo | partial code }}
- **Target Length**: {{ e.g., ~1200–1800 words }}
- **Must-include Sections**: {{ e.g., Deployment to Vercel, CI with GitHub Actions }}
- **Links to Prefer**: {{ official docs, sample repos }}
- **Image/Diagram Needs**: {{ optional ASCII diagram or screenshot description }}

### Output Contract

Produce exactly one markdown tutorial using this section order:

1. Overview
2. Prerequisites
3. What You’ll Build
4. Quick Start
5. Step-by-Step (each step: Goal, Actions, Code, Expected Result, Why it works, Troubleshooting)
6. Wrap-up
7. Appendix (optional)

### Authoring Notes

- Use second person, active voice, concise tone.
- Show complete minimal files; never omit imports or critical config.
- Pin versions where important; include `--version` checks.
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
