---
title: "A First-Day PHP Team Onboarding Path"
description: "Give new PHP teammates four practical exercises for installing dependencies, tracing a request, running tests, and shipping a small change."
difficulty: "Intermediate"
author: "Code with PHP"
datePublished: "2026-11-20"
prev: false
next: false
---

Use this path when joining a PHP team or welcoming someone to yours. By the end, the new teammate can run the app, explain one request, and show a small change with a passing check. Use a local development environment and sample data.

## Install the project and record one known-good command

Read the team README and CI setup. Record the PHP branch, required extensions, and the command that starts the app. For a project with a committed `composer.lock`, use the team's `composer install` command to install the recorded dependency versions. Open `composer.json` and find one package in `require` and one namespace in `autoload`. Confirm what each does. [Composer and dependencies](/series/php-basics/chapters/12-dependency-management-with-composer/) · [Compare PHP environments](/learn/local-vs-hosted-php/)

**Done when** a teammate can repeat your startup command and see the expected page or console output.

## Trace one request

Pick a small read-only page or JSON endpoint. Find its route definition, handler, and response. Write a three-line map that names the files you opened. [Follow a request in the router demo](/learn/read-a-php-codebase/)

**Done when** you can point to the exact line that controls one field or heading in the response.

## Run one focused test

Use the team's test command to run the test nearest that behaviour. Read the input or fixture and the expected result. Run the test before editing to establish a passing baseline. For independent practice, use the Laravel quote demo and run `php artisan test --filter=QuoteTest` inside its demo app. You should see two passing cases. [Laravel quote feature and tests](/series/php-misconceptions/chapters/04-clean-laravel-code/)

**Done when** you can explain what the test checks and what would make it fail.

## Make a small change and show the result

Choose a change with one clear outcome, such as a copy correction or a new test for existing behaviour. Rerun the focused test and the project's documented checks. Prepare a short review note with the changed behaviour, the test command, and the observed result. For the quote demo, change the seat price to 1,500 cents and confirm the updated test expects 4,500 cents for three seats. [PHP developer tooling](/series/php-misconceptions/chapters/11-developer-tooling/)

**Done when** another developer can reproduce the result from your note.
