# PHP From Scratch

A tutorial-based learning resource to help developers fall in love with PHP and the PHP ecosystem. Fully open source (MIT).

- Website powered by VitePress 1.6.4
- Content lives under `docs/`
- Each series is independent with chapters and colocated code samples

## Quickstart

1. Install dependencies

```bash
npm install
```

2. Start local server

```bash
npm run dev
```

3. Build static site

```bash
npm run build
```

4. Preview production build

```bash
npm run preview
```

## Structure

- `docs/` — VitePress content root
  - `.vitepress/config.ts` — site configuration
  - `index.md` — landing page
  - `series/<series-slug>/index.md` — series overview
  - `series/<series-slug>/chapters/<nn>-<chapter-slug>.md` — numbered chapters
  - `series/<series-slug>/code/` — code samples for chapters

## Contributing

- Edit pages via the "Edit this page" link or on GitHub.
- Follow authoring rules in `.cursor/rules/`.

## Links

- Docs: `docs/`
- VitePress: https://vitepress.dev
- Repo: https://github.com/dalehurley/PHP-From-Scratch

## License

MIT
