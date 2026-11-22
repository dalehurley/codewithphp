---
title: "Contribute a PHP Example"
description: "Make your first Code with PHP contribution. Find a lesson, run its PHP example, check the rendered page, and open a pull request."
dateModified: "2026-11-22"
prev: false
next: false
---

Pick a [good first issue](https://github.com/dalehurley/codewithphp/labels/good%20first%20issue) or improve a lesson you’ve tried. Adding one missing expected result is enough for a first contribution.

## Set up your copy

[Fork Code with PHP on GitHub](https://github.com/dalehurley/codewithphp/fork). Replace `YOUR-USERNAME` with your GitHub username and run these commands.

```sh
git clone https://github.com/YOUR-USERNAME/codewithphp.git
cd codewithphp
git switch -c docs/example-output
```

Find the lesson’s source using its **Edit page** link. The lesson Markdown lives in `src/content/docs/`. Examples usually live in the repository’s `code/` directory. Use the file link in the lesson to find its example. [GitHub guide to forking](https://docs.github.com/en/pull-requests/how-tos/work-with-forks/fork-a-repo)

## Reproduce the result

Set up [PHP 8.6](/series/php-basics/chapters/00-setting-up-your-development-environment/). Run these commands from the repository root to check the return value in the [PHP functions lesson](/series/php-basics/chapters/04-understanding-and-using-functions/).

```sh
php -l code/php-basics/04-functions/basic-functions.php
php code/php-basics/04-functions/basic-functions.php
```

The first command should report no syntax errors. Look for these two lines in the second command’s output.

```text
3. Function with Return Value:
5 + 10 = 15
```

Find the corresponding lesson Markdown under `src/content/docs/series/php-basics/chapters/`. An edit could name that section of the output and show the expected line beside the example. If you add a new example, keep it with the lesson’s existing source files. In the lesson, show the command and expected result. Include any input or PHP extensions needed to run it.

## Check the page and open a pull request

Use Node.js 22, the version used by the site’s build workflow. Run these checks from the repository root.

```sh
npm ci
npm run build
npm run check:seo
git diff --check
```

Use `npm run preview` and open the updated lesson if you changed how an example appears. Commit your changes, then push the branch to your fork with `git push -u origin docs/example-output`. Open a pull request into `dalehurley/codewithphp:main` and include the lesson URL, the command you ran, and the relevant output. Link the issue if there is one. Include the name you would like us to use if we thank you in the [site updates](/updates/). [GitHub guide to pull requests from forks](https://docs.github.com/en/pull-requests/how-tos/create-pull-requests/creating-a-pull-request-from-a-fork)

Use **Report a problem with this example** on a chapter or **Report a problem with this page** on a guide to report a broken command. [Ask a question in GitHub Discussions](https://github.com/dalehurley/codewithphp/discussions/categories/q-a) if you’re stuck, or [suggest what we should explain next](https://github.com/dalehurley/codewithphp/discussions/35).
