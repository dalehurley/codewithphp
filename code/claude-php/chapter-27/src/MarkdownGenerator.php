<?php

declare(strict_types=1);

namespace DocsGenerator;

/**
 * Markdown Documentation Generator
 */
class MarkdownGenerator
{
    /**
     * Generate markdown for class documentation
     */
    public function generateClassMarkdown(array $classInfo, string $aiDocs): string
    {
        $md = "# {$classInfo['name']}\n\n";
        $md .= $aiDocs . "\n\n";

        // Properties
        if (!empty($classInfo['properties'])) {
            $md .= "## Properties\n\n";
            foreach ($classInfo['properties'] as $prop) {
                $md .= "### {$prop['visibility']} \${$prop['name']}\n\n";
                $md .= "Type: `{$prop['type']}`\n\n";
                if ($prop['docComment']) {
                    $md .= $this->cleanDocComment($prop['docComment']) . "\n\n";
                }
            }
        }

        // Methods
        if (!empty($classInfo['methods'])) {
            $md .= "## Methods\n\n";
            foreach ($classInfo['methods'] as $method) {
                $md .= "### {$method['visibility']} {$method['name']}()\n\n";
                $md .= "```php\n";
                $md .= $this->generateMethodSignature($method) . "\n";
                $md .= "```\n\n";

                if ($method['docComment']) {
                    $md .= $this->cleanDocComment($method['docComment']) . "\n\n";
                }

                // Parameters
                if (!empty($method['params'])) {
                    $md .= "**Parameters:**\n\n";
                    foreach ($method['params'] as $param) {
                        $md .= "- `\${$param['name']}` ({$param['type']})";
                        if ($param['default']) {
                            $md .= " = {$param['default']}";
                        }
                        $md .= "\n";
                    }
                    $md .= "\n";
                }

                // Return type
                $md .= "**Returns:** `{$method['returnType']}`\n\n";
            }
        }

        return $md;
    }

    /**
     * Generate method signature
     */
    private function generateMethodSignature(array $method): string
    {
        $params = array_map(
            fn($p) => "{$p['type']} \${$p['name']}",
            $method['params']
        );

        return "{$method['visibility']} function {$method['name']}("
            . implode(', ', $params)
            . "): {$method['returnType']}";
    }

    /**
     * Clean doc comment
     */
    private function cleanDocComment(string $comment): string
    {
        $lines = explode("\n", $comment);
        $cleaned = array_map(
            fn($line) => trim(ltrim(trim($line), '*/ ')),
            $lines
        );
        return implode("\n", array_filter($cleaned));
    }

    /**
     * Generate table of contents
     */
    public function generateTableOfContents(array $structure): string
    {
        $toc = "# Table of Contents\n\n";

        foreach ($structure as $file => $data) {
            $relativePath = basename($file);
            $toc .= "## {$relativePath}\n\n";

            foreach ($data['classes'] as $class) {
                $toc .= "- [{$class['name']}](#{$this->slugify($class['name'])})\n";
            }

            foreach ($data['functions'] as $function) {
                $toc .= "- [{$function['name']}()](#{$this->slugify($function['name'])})\n";
            }

            $toc .= "\n";
        }

        return $toc;
    }

    /**
     * Slugify text for anchors
     */
    private function slugify(string $text): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $text));
    }
}
