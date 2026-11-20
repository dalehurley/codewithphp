---
title: "How Code with PHP Keeps Its Tutorials Working"
description: "A case study in fixing broken PHP tutorial links, replacing unfinished lessons, and checking the result on a static site."
prev: false
next: false
---

Code with PHP has hundreds of lessons in one open repository. A routine update can move a chapter, leave an old link behind, or quietly introduce a second address for the same tutorial. During a November 2026 audit, our crawl found **73 missing destinations linked from published pages**. Four Laravel CRM topics also had unfinished lesson pages.

## What changed

We used the existing Astro build to check the output readers and search engines receive. The check starts with the sitemap, opens each listed page, and looks for missing destinations, invalid canonical URLs, missing descriptions, and missing social previews. It also checks the Markdown copies and the redirect target map. GitHub Actions runs it on every pull request and before publishing. [Read the checker](https://github.com/dalehurley/codewithphp/blob/main/scripts/check-site-seo.mjs)

For the Laravel course, we replaced four placeholders with examples for a REST API, Sanctum, queues, and Horizon. We kept drafts out of the live catalogue and mapped three retired chapter addresses to their replacements. GitHub Pages serves these as HTML redirects, so the checker opens each old file and verifies its target. [Read the changes in pull request #31](https://github.com/dalehurley/codewithphp/pull/31)

## What we measured

The release check covered **328 sitemap pages**. A fresh public crawl found that none still linked to the 73 missing destinations from the audit. Those numbers describe the November release. We use Search Console and Cloudflare Web Analytics for impressions, clicks, and referrals over time. [Build and deployment](https://github.com/dalehurley/codewithphp/actions/runs/44623827156)

One lesson from the work was to check finished HTML as well as source Markdown. Astro generates some navigation, metadata, and redirects at build time. Checking those files catches problems that a scan of Markdown links would miss.

To try the same check, clone the [Code with PHP repository](https://github.com/dalehurley/codewithphp), install the Node dependencies with `npm ci`, and run:

```sh
npm run build
npm run check:seo
```
