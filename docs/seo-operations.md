# Search and referral measurement

## Search Console

Use the [codewithphp.com domain property](https://search.google.com/search-console?resource_id=sc-domain%3Acodewithphp.com) for coverage across hosts and protocols. Grant access from the existing property owner account or verify domain ownership with DNS. An HTTPS URL-prefix property can use the HTML tag method when DNS access is unavailable. Set `PUBLIC_GOOGLE_SITE_VERIFICATION` as a GitHub Actions repository variable to the tag's content value, deploy, then complete verification in Google. [Google verification methods](https://support.google.com/webmasters/answer/9008080)

Submit `https://codewithphp.com/sitemap-index.xml`. The legacy `sitemap.xml` URL serves the same index. Inspect the homepage, PHP Basics, the 8.6 upgrade guide, and one of the rewritten Laravel lessons. Compare the declared and Google-selected canonical. Record the sitemap read status and any page indexing exclusions for those routes. [Google sitemap guidance](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap)

Export the most recent 90 days and preceding 90 days of Web search data when available. Compare clicks and impressions by query, landing page and device, and save the Links report. Track branded variations of Code with PHP independently from generic queries such as `coding with PHP`, `learn PHP` and `PHP 8.6 upgrade checklist`. Keep the PHP Basics, PHP 8.6 and Laravel CRM landing-page cohorts distinct so a launch spike does not mask an evergreen trend. [Google Search performance report](https://support.google.com/webmasters/answer/7576553)

## Referrals and mobile field data

Enable Cloudflare Web Analytics for `codewithphp.com` using manual JS snippet installation. Add the public beacon site token to the GitHub Actions repository variable `PUBLIC_CLOUDFLARE_WEB_ANALYTICS_TOKEN` and deploy. A visit should load `static.cloudflareinsights.com/beacon.min.js` and appear in Web Analytics. The build emits the script only when that variable has a value. Cloudflare Web Analytics reports referrers and page performance. Use an event-capable collector if lesson-start, next-lesson and sample-code click events need individual attribution. [Cloudflare setup](https://developers.cloudflare.com/web-analytics/get-started/) · [Beacon behaviour](https://developers.cloudflare.com/web-analytics/faq/)

Use the landing path and referring site to monitor developer newsletters, GitHub and AI search referrals. Google Search Console remains the source for search impressions, clicks and page indexing. Compare 28-day windows after deployment and account for the PHP release date. Lighthouse runs provide a lab baseline. Assess real-user LCP, INP and CLS at the 75th percentile as field data becomes available. [Core Web Vitals](https://web.dev/articles/vitals)

## Release validation

Run `npm run build` followed by `npm run check:seo`. The checker covers all sitemap pages and tests metadata, breadcrumbs, social images, internal links, Markdown alternates and configured redirect targets. The GitHub Actions build runs the same check before deployment.

The Markdown routes and `/llms.txt` are generated from published Markdown-backed course entries. Treat the HTML page as the public citation URL. Google's AI search features use the same indexed site and content as Search. [Google AI search guidance](https://developers.google.com/search/docs/fundamentals/ai-optimization-guide)

GitHub Pages serves the three retired CRM URLs as immediate HTML redirects to the completed lessons. Google recognises this form as permanent. For a transport-level 301, apply the same mappings at a host or edge that supports custom redirects, then validate status and target from the public hostname. [Google redirect guidance](https://developers.google.com/search/docs/crawling-indexing/301-redirects)
