<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\File;

/**
 * Prompt Manager for template-based prompts
 */
class PromptManager
{
    private const CACHE_PREFIX = 'claude:prompt:';

    public function __construct(
        private readonly string $path,
        private readonly ?CacheContract $cache = null
    ) {
    }

    /**
     * Load a prompt template
     */
    public function load(string $name): string
    {
        $cacheKey = self::CACHE_PREFIX . $name;

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $path = $this->getPromptPath($name);

        if (!File::exists($path)) {
            throw new \RuntimeException("Prompt template not found: {$name}");
        }

        $content = File::get($path);

        if ($this->cache) {
            $this->cache->forever($cacheKey, $content);
        }

        return $content;
    }

    /**
     * Load and fill a prompt template
     */
    public function fill(string $name, array $variables = []): string
    {
        $template = $this->load($name);

        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", (string)$value, $template);
        }

        return $template;
    }

    /**
     * Save a prompt template
     */
    public function save(string $name, string $content): void
    {
        $path = $this->getPromptPath($name);
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($path, $content);

        // Clear cache
        if ($this->cache) {
            $this->cache->forget(self::CACHE_PREFIX . $name);
        }
    }

    /**
     * List all prompt templates
     */
    public function list(): array
    {
        if (!File::isDirectory($this->path)) {
            return [];
        }

        $files = File::allFiles($this->path);

        return array_map(function ($file) {
            return str_replace(
                [$this->path . '/', '.txt', '.md'],
                '',
                $file->getPathname()
            );
        }, $files);
    }

    /**
     * Get the full path to a prompt file
     */
    private function getPromptPath(string $name): string
    {
        // Try .txt first, then .md
        $txtPath = $this->path . '/' . $name . '.txt';
        $mdPath = $this->path . '/' . $name . '.md';

        if (File::exists($txtPath)) {
            return $txtPath;
        }

        if (File::exists($mdPath)) {
            return $mdPath;
        }

        // Default to .txt for new files
        return $txtPath;
    }
}
