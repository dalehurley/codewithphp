<template>
  <section class="py-24 bg-white dark:bg-gray-950">
    <div class="container mx-auto px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Why PHP in 2026?
          </h2>
          <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            PHP has evolved. Here's why it's still one of the best choices for web development.
          </p>
        </div>

        <!-- Two column layout -->
        <div class="grid lg:grid-cols-2 gap-16 items-start">
          <!-- Left: Stats and facts -->
          <div class="space-y-8">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center shrink-0">
                <span class="text-lg font-bold text-gray-900 dark:text-white">77%</span>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Powers the web</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                  PHP runs on 77% of all websites with known server-side languages. WordPress, Wikipedia, Slack's backend - all PHP.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">High demand, great salary</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                  Laravel developers are in demand worldwide. Companies need engineers who understand the ecosystem deeply.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Ship fast, iterate faster</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                  PHP's simplicity means you can prototype ideas quickly and refactor as you learn. No compile step, instant feedback.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Battle-tested at scale</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                  Facebook built their empire on PHP (now Hack). Etsy, Mailchimp, and thousands of startups prove it scales.
                </p>
              </div>
            </div>
          </div>

          <!-- Right: Code comparison -->
          <div class="space-y-6">
            <div class="bg-gray-900 dark:bg-gray-950 rounded-xl border border-gray-800 overflow-hidden">
              <div class="flex items-center px-4 py-3 bg-gray-800/50 border-b border-gray-800">
                <span class="text-xs text-gray-500 font-mono">Modern PHP 8.4</span>
              </div>
              <div class="p-5 overflow-x-auto">
                <pre class="text-sm leading-relaxed font-mono"><code><span class="text-gray-500">// Property hooks (PHP 8.4)</span>
<span class="text-purple-400">class</span> <span class="text-yellow-300">User</span>
<span class="text-gray-400">{</span>
    <span class="text-purple-400">public string</span> <span class="text-blue-300">$email</span> <span class="text-gray-400">{</span>
        <span class="text-purple-400">set</span> => <span class="text-yellow-300">strtolower</span>(<span class="text-orange-300">$value</span>);
    <span class="text-gray-400">}</span>

    <span class="text-purple-400">public function</span> <span class="text-blue-300">__construct</span>(
        <span class="text-purple-400">public readonly string</span> <span class="text-blue-300">$name</span>,
        <span class="text-purple-400">public</span> <span class="text-green-400">Role</span> <span class="text-blue-300">$role</span> = <span class="text-green-400">Role</span>::<span class="text-orange-300">User</span>,
    ) <span class="text-gray-400">{}</span>
<span class="text-gray-400">}</span>

<span class="text-gray-500">// Expressive, type-safe, clean</span>
<span class="text-orange-300">$user</span> = <span class="text-purple-400">new</span> <span class="text-green-400">User</span>(<span class="text-green-300">'Alice'</span>);
<span class="text-orange-300">$user</span>-><span class="text-blue-300">email</span> = <span class="text-green-300">'ALICE@EXAMPLE.COM'</span>;
<span class="text-gray-500">// email is automatically lowercased</span></code></pre>
              </div>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
              This isn't your 2005 PHP. It's elegant, typed, and powerful.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Why PHP Section - Making the case for modern PHP
</script>
