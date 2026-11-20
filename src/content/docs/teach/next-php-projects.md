---
title: "What to Build Next After Learning PHP"
description: "Pick a concrete next project after PHP Basics or the Laravel CRM. Extend a plain PHP app, rebuild a feature in Laravel, or add automated tests."
prev: false
next: false
---

After your first PHP project, pick a change you can finish and demonstrate. Use the [project finish-line checks](/teach/project-checklists/) if you want to verify your starting point first.

## Keep building in plain PHP

Add title search to your blog. Start with a GET form on the post list so the query is part of the URL. Read the blog’s [post-search exercise](/series/php-basics/chapters/19-project-building-a-simple-blog/#add-post-search) for the model and controller changes.

The feature is finished when a search for a unique word shows the expected post, a search for a missing word shows an empty result, and clearing the search restores the full list.

## Rebuild a feature in Laravel

Recreate the reading list using Laravel routes, a Blade view, and a database model. Start with a route that lists books from SQLite and a form that adds one. The [Laravel introduction](/series/php-basics/chapters/20-a-gentle-introduction-to-laravel/) covers the pieces.

Check that you can add a book, reload the page, and see it listed. Compare the PHP starter and Laravel versions to see where each reads input and queries SQLite.

## Learn to test a feature

Extract the reading list’s title trimming into a function and add an automated check. Use the [small PHP testing workflow](/teach/php-test-workflow/).

Check that your local test reports four successful cases, a deliberate change makes one fail, and GitHub Actions runs the test on both versions in your matrix.

## Go further with the CRM

Try the [Laravel CRM contacts API lab](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/) if you finished the main app. If you already finished the queue lab, try exporting just a subset of your test contacts. Start by defining a clear filter, such as an email address prefix, and adding it to the [export job](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/).

Check that the exported file contains the matching test contact and leaves out a contact that doesn’t match. Run the lab’s retry test to confirm that repeated execution for the same export ID still writes one file.
