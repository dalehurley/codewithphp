---
title: "Which PHP Version Should I Use?"
description: "Choose a supported PHP version for learning, starting a project, maintaining an application, or matching a web host."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
prev: false
next: false
---

For a new PHP project, start with the latest patch of an actively supported release that your host and dependencies support. PHP 8.6 is the newest stable branch as of 20 November 2026 and the version used by PHP Basics. Check PHP's release pages for the latest patch and current support dates. [PHP downloads](https://www.php.net/downloads.php) · [Supported PHP versions](https://www.php.net/supported-versions.php)

## Choose for the project you have

| Situation | What to do |
| --- | --- |
| You are learning locally | Install the latest PHP 8.6 patch to follow [PHP Basics](/series/php-basics/). Use the [setup lesson](/series/php-basics/chapters/00-setting-up-your-development-environment/) to verify your terminal and start a local server. |
| You are starting a new app | Check your framework and packages for supported PHP branches and required extensions. Choose a branch that is actively supported and available in the environment where you will run the app. |
| You are maintaining an existing app | Start with the version and extensions in the deployment and CI files. Plan a separate upgrade when compatibility and your release schedule allow. Test each affected workflow before changing the deployed runtime. |
| Your host supplies PHP | Read the selected version for the specific site or web service. Match that branch and its extensions in development. When the host only offers an end-of-life branch, plan to move the app to a supported runtime. |

A release can still receive critical security fixes after its active support period. Record the end-of-security-support date when choosing a branch. [PHP release support policy](https://www.php.net/supported-versions.php)

## Confirm what is running

At the command line in your project, run the following.

```sh
php -v
php --ini
php -m
```

These commands report the terminal's PHP version, loaded configuration files, and extensions. If the site runs in a container, run the commands in the PHP container. Compare with the web runtime through your host's dashboard or staging configuration. [Diagnose local and hosted PHP differences](/learn/local-vs-hosted-php/)

For an installed Composer project, check whether the current command-line runtime meets the packages' requirements.

```sh
composer check-platform-reqs
```

A pass means that this PHP process satisfies the package constraints Composer checks. Keep testing application behaviour on the target runtime. When moving from 8.5 to 8.6, use the [upgrade checklist](/series/php-8-6/upgrade-checklist/). For earlier versions, review each intervening migration guide. [Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs) · [PHP migration guides](https://www.php.net/manual/en/migration86.php)
