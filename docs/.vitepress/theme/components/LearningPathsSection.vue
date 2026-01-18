<template>
  <section id="paths" class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-3">
            Choose your path
          </h2>
          <p class="text-gray-600 dark:text-gray-400">
            10 learning paths for every background and goal.
          </p>
        </div>

        <!-- Paths Grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <!-- PHP Basics - Primary -->
          <a href="/series/php-basics/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border-2 border-teal-500 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400">
                PHP Basics
              </h3>
              <span class="text-xs font-medium text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2 py-0.5 rounded">Start here</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Zero to hero. Build a blog, router, and MVC framework.
            </p>
          </a>

          <!-- Algorithms -->
          <a href="/series/php-algorithms/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              Algorithms
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Data structures and CS fundamentals for interviews.
            </p>
          </a>

          <!-- Laravel 12 -->
          <a href="/series/build-crm-laravel-12/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              Laravel 12 CRM
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Build a production CRM with Laravel and React.
            </p>
          </a>

          <!-- AI/ML -->
          <a href="/series/ai-ml-php-developers/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              AI & Machine Learning
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Build intelligent PHP apps with ML.
            </p>
          </a>

          <!-- Claude for PHP -->
          <a href="/series/claude-php-developers/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              Claude API
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Integrate Claude AI into PHP applications.
            </p>
          </a>

          <!-- Data Science -->
          <a href="/series/data-science-php-developers/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              Data Science
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Data analysis and visualization in PHP.
            </p>
          </a>

          <!-- For Java Devs -->
          <a href="/series/php-for-java-developers/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              From Java
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Translate your OOP knowledge to PHP.
            </p>
          </a>

          <!-- For TS Devs -->
          <a href="/series/php-typescript-developers/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              From TypeScript
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Leverage your type system experience.
            </p>
          </a>

          <!-- For Python Devs -->
          <a href="/series/python-developers-love-php-laravel/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              From Python
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Python dev? You'll love Laravel.
            </p>
          </a>

          <!-- For Rails Devs -->
          <a href="/series/rails-developers-love-laravel/"
             class="group block p-5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400">
              From Rails
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Rails conventions, Laravel style.
            </p>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Learning Paths - All 10 courses
</script>
