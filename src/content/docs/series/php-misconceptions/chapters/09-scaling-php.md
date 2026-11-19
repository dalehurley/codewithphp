---
title: "09: Can PHP Scale?"
description: "Scale a Laravel application across identical instances and calculate whether web workers or shared infrastructure set the limit."
series: "php-misconceptions"
chapter: 9
estimatedTime: "PT10M"
sidebar:
  order: 9
---

## Overview

A Laravel app can serve traffic from multiple copies of the same deployment. A load balancer can send one request to instance A and the next to instance B. Keep the state they both need in services they can both reach. You can do this with a single application and one codebase. [Laravel deployment](https://laravel.com/docs/13.x/deployment) · [Laravel Cloud compute](https://cloud.laravel.com/docs/compute)

## Keep requests interchangeable

In a one-instance deployment, a local session file and uploaded photos may appear to work. Add a second instance and both the login state and the photo must still be available whichever process responds. Put sessions in a shared database or Redis and use shared object storage for uploads. Configure the same `APP_KEY` on every instance in that deployment so each can decrypt the session cookie. Shared caching can reduce repeated work. Pick a cache key and expiry that preserve the data’s tenant and freshness boundaries. [Laravel sessions](https://laravel.com/docs/13.x/session) · [Laravel encryption](https://laravel.com/docs/13.x/encryption) · [Laravel filesystem](https://laravel.com/docs/13.x/filesystem) · [Laravel cache](https://laravel.com/docs/13.x/cache)

Move work such as report generation into a queue if the request can return before the report is ready. Web and queue capacity can then increase independently. Design jobs so a retry does not send the same email twice or repeat a charge. Watch queue age, database connections and p95 latency as well as request rate. [Laravel queues](https://laravel.com/docs/13.x/queues)

## Exercise

Assume a web instance has eight workers and a request occupies a worker for 100 ms. At full utilisation and with perfectly even work, that is approximately 80 requests per second. Three instances give an upper bound of 240 requests per second under those assumptions.

Now assume each busy worker holds one database connection. A total budget of 25 connections with six reserved for queue and admin work leaves 19 for web workers across the cluster. With eight workers per instance, three instances can ask for 24 web connections, five above that limit. Six workers per instance would use up to 18 web connections and give an idealised ceiling of 180 requests per second at the same 100 ms per request. Verify the resulting capacity under a representative load with headroom for slow requests.

## Next chapter

[Can PHP Do Concurrency?](/series/php-misconceptions/chapters/10-php-concurrency/)
