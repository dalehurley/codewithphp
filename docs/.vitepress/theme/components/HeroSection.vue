<template>
  <section class="pt-16 pb-8 lg:pt-24 lg:pb-12 bg-white dark:bg-gray-950">
    <div class="container mx-auto px-6 lg:px-8">
      <div class="max-w-4xl mx-auto text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 font-mono mb-6 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-full">
          <span class="w-2 h-2 bg-teal-500 rounded-full"></span>
          <span>Open Source · PHP 8.4 · Free Forever</span>
        </div>

        <!-- Main Heading -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900 dark:text-white leading-[1.1] mb-6">
          Learn PHP by building,<br>
          <span class="text-teal-600 dark:text-teal-400">not by copying.</span>
        </h1>

        <!-- Description -->
        <div class="max-w-2xl mx-auto mb-8">
          <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 leading-relaxed text-center">
            Create your own router, ORM, and MVC framework from scratch. Then graduate to Laravel with deep understanding of what's happening under the hood.
          </p>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-12">
          <a href="/series/php-basics/"
             class="inline-flex items-center justify-center px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-medium rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
            Start Building
            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>

          <a href="https://github.com/codewithphp/php-from-scratch"
             target="_blank"
             rel="noopener"
             class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
            </svg>
            Star on GitHub
          </a>
        </div>

        <!-- Code Preview -->
        <div class="max-w-2xl mx-auto">
          <div class="bg-gray-900 dark:bg-black rounded-xl border border-gray-800 overflow-hidden shadow-2xl text-left">
            <div class="flex items-center justify-between px-4 py-2.5 bg-gray-800/60 border-b border-gray-800">
              <div class="flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 bg-gray-600 rounded-full"></div>
                <div class="w-2.5 h-2.5 bg-gray-600 rounded-full"></div>
                <div class="w-2.5 h-2.5 bg-gray-600 rounded-full"></div>
              </div>
              <span class="text-xs text-gray-500 font-mono">Router.php</span>
              <div class="w-10"></div>
            </div>

            <div class="p-4 overflow-x-auto">
              <pre class="text-[13px] leading-relaxed font-mono"><code><span class="text-gray-500">// You'll build this from scratch</span>
<span class="text-purple-400">class</span> <span class="text-yellow-300">Router</span> <span class="text-gray-500">{</span>
    <span class="text-purple-400">public function</span> <span class="text-blue-300">get</span>(<span class="text-green-400">string</span> <span class="text-orange-300">$path</span>, <span class="text-green-400">callable</span> <span class="text-orange-300">$handler</span>): <span class="text-green-400">void</span> <span class="text-gray-500">{</span>
        <span class="text-blue-300">$this</span>-><span class="text-blue-300">routes</span>[<span class="text-green-300">'GET'</span>][<span class="text-orange-300">$path</span>] = <span class="text-orange-300">$handler</span>;
    <span class="text-gray-500">}</span>
<span class="text-gray-500">}</span>

<span class="text-orange-300">$router</span> = <span class="text-purple-400">new</span> <span class="text-yellow-300">Router</span>();
<span class="text-orange-300">$router</span>-><span class="text-blue-300">get</span>(<span class="text-green-300">'/users'</span>, <span class="text-orange-300">$userController</span>(...));</code></pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Hero - Centered layout per dev tool best practices
</script>
