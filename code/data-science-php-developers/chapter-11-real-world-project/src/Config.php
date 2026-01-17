<?php

declare(strict_types=1);

namespace SmartRecommender;

class Config
{
    private static array $config = [];
    
    public static function load(string $file): void
    {
        $path = __DIR__ . '/../config/' . $file . '.php';
        
        if (!file_exists($path)) {
            throw new \RuntimeException("Config file not found: {$file}");
        }
        
        self::$config[$file] = require $path;
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key (e.g., 'app.name')
     * @param mixed $default Default value if not found
     * @return mixed Configuration value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Configuration key cannot be empty');
        }
        
        $parts = explode('.', $key);
        $file = array_shift($parts);
        
        if (!isset(self::$config[$file])) {
            self::load($file);
        }
        
        $value = self::$config[$file];
        
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        
        return $value;
    }
}
