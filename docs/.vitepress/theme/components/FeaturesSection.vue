<template>
  <section class="py-20 bg-white dark:bg-gray-950">
    <div class="container mx-auto px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-14">
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-3">
            Why learn PHP this way?
          </h2>
          <p class="text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
            Most tutorials teach you to copy code. We teach you to understand it.
          </p>
        </div>

        <!-- 3 Core Features -->
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
          <!-- Feature 1 -->
          <div>
            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build first, framework later</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
              Create your own router, database layer, and templating engine. When you finally use Laravel, you'll understand every line.
            </p>
          </div>

          <!-- Feature 2 -->
          <div>
            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Modern PHP only</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
              PHP 8.4 with property hooks, readonly classes, enums, and typed properties. No legacy patterns or deprecated code.
            </p>
          </div>

          <!-- Feature 3 -->
          <div>
            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Production ready</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
              Every example is tested. Build a complete blog, then graduate to a production CRM with Laravel 12 and React.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Features - Problem-focused, not feature lists
</script>
