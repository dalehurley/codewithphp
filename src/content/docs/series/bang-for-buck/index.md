---
title: "Bang for Buck: Scaling PHP Apps"
description: "Measure a Laravel application, remove expensive work, and find the lowest-cost deployment that meets your targets."
---

When an application slows down, you can pay for a larger server, change the code, or both. This series gives you a way to compare those choices. You’ll use a small Laravel shop to establish how much correct work it can do, find what limits it, and measure each change against the same traffic.

The shop includes a product catalogue, orders that need a shipping quote, and a worker that produces CSV exports. The lab starts with PHP-FPM, OPcache and PostgreSQL. The benchmark captures what clients see and what the application saves, so you can compare response times, completed work and resource use.

## Start measuring

1. [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/) — Start the shop and record how it handles a steady workload.
2. [Test Steady Traffic, Bursts and Cold Starts](/series/bang-for-buck/chapters/02-test-realistic-traffic/) — Find where targets start to slip and see how the app recovers.
3. [Profile a Slow Laravel Request](/series/bang-for-buck/chapters/03-profile-a-slow-request/) — Trace SQL, shipping and PHP CPU time, then reproduce the bottleneck under load.
4. [Cut Avoidable Work on Every Deploy](/series/bang-for-buck/chapters/04-cut-deployment-work/) — Measure the Laravel deployment caches and OPcache on cold and warm requests.
5. [Ask the Database for Less](/series/bang-for-buck/chapters/05-ask-the-database-for-less/) — Fix repeated queries, select narrower rows and measure cursor pagination.
6. [Make the Remaining SQL Cheaper](/series/bang-for-buck/chapters/06-make-the-remaining-sql-cheaper/) — Read a PostgreSQL plan and measure a composite index on live reads and writes in the lab.
7. [Cache Data with an Expiry and an Owner](/series/bang-for-buck/chapters/07-cache-data-with-an-expiry-and-an-owner/) — Cache a catalogue summary in Redis and measure SQL load during hits, expiry and recovery.
8. [Move Work Beyond the Response](/series/bang-for-buck/chapters/08-move-work-beyond-the-response/) — Compare synchronous and queued CSV exports from the first response to the finished download.

You’ll need a local Code with PHP checkout, Docker with Compose, Bash and curl. The model shop and load generator run inside containers. The later parts of the series use the same app to measure database and cache changes, compare PHP runtimes and test scaling choices.
