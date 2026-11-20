---
title: "Contribute a PHP Example"
description: "Add one runnable PHP example, report an error, or help make a Code with PHP lesson clearer and more accessible."
prev: false
next: false
---

Pick a [good first issue](https://github.com/dalehurley/codewithphp/labels/good%20first%20issue) or improve an example in a lesson you’ve tried.

1. **Find the files.** Use the lesson’s **Edit page** link to find its Markdown under `src/content/docs/`. Follow the lesson’s repository link to its examples. Keep a new file alongside the existing examples under `code/<series>/`, or follow the lesson’s existing arrangement.
2. **Make one small change.** Add a complete example with sample input, a command readers can copy, and the expected output. Use fictional input and the PHP version and extensions listed in the lesson. For alternate solutions, show how to run each one and when you’d choose it.
3. **Run the code.** From the repository root, use `php -l path/to/example.php` to check syntax, then run the same command you put in the lesson. Include any setup steps, input files, and command output in your pull request.
4. **Check the page.** Run `npm ci`, `npm run build`, and `npm run check:seo` from the repository root. Open the lesson in the preview with `npm run preview` if you changed how an example is displayed.
5. **Open a pull request.** Link to the lesson and explain what a reader can now do. Include the name you would like us to use if we thank you in the [site updates](/updates/).

Use **Report a problem with this example** at the end of a chapter to report a broken command. [Ask a question in GitHub Discussions](https://github.com/dalehurley/codewithphp/discussions/categories/q-a) if you’re stuck, or [suggest what we should explain next](https://github.com/dalehurley/codewithphp/discussions/35).
