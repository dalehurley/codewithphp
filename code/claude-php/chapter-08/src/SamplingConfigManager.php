<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class SamplingConfigManager
{
    private array $configs = [];
    private string $currentConfig = 'default';

    public function __construct()
    {
        // Load default configurations
        $this->registerConfig('default', [
            'temperature' => 1.0,
            'top_p' => 0.9,
        ]);

        $this->registerConfig('deterministic', [
            'temperature' => 0.0,
            'top_p' => 1.0,
        ]);

        $this->registerConfig('creative', [
            'temperature' => 1.5,
            'top_p' => 0.95,
        ]);
    }

    public function registerConfig(string $name, array $config): void
    {
        $this->validateConfig($config);
        $this->configs[$name] = $config;
    }

    public function useConfig(string $name): void
    {
        if (!isset($this->configs[$name])) {
            throw new \InvalidArgumentException("Config '{$name}' not found");
        }
        $this->currentConfig = $name;
    }

    public function getConfig(?string $name = null): array
    {
        $name = $name ?? $this->currentConfig;
        return $this->configs[$name] ?? $this->configs['default'];
    }

    public function mergeConfig(array $overrides): array
    {
        return array_merge($this->getConfig(), $overrides);
    }

    private function validateConfig(array $config): void
    {
        if (isset($config['temperature'])) {
            $temp = $config['temperature'];
            if ($temp < 0 || $temp > 2) {
                throw new \InvalidArgumentException('Temperature must be between 0 and 2');
            }
        }

        if (isset($config['top_p'])) {
            $topP = $config['top_p'];
            if ($topP < 0 || $topP > 1) {
                throw new \InvalidArgumentException('top_p must be between 0 and 1');
            }
        }
    }

    public function listConfigs(): array
    {
        return array_keys($this->configs);
    }
}

