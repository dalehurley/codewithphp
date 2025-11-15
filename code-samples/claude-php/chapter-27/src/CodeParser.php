<?php

declare(strict_types=1);

namespace DocsGenerator;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * PHP Code Parser for documentation generation
 */
class CodeParser
{
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * Parse PHP file and extract documentation structure
     */
    public function parseFile(string $filepath): array
    {
        $code = file_get_contents($filepath);
        $ast = $this->parser->parse($code);

        $visitor = new class extends NodeVisitorAbstract {
            public array $classes = [];
            public array $functions = [];
            public array $constants = [];

            public function enterNode(Node $node) {
                if ($node instanceof Node\Stmt\Class_) {
                    $this->classes[] = $this->extractClass($node);
                } elseif ($node instanceof Node\Stmt\Function_) {
                    $this->functions[] = $this->extractFunction($node);
                } elseif ($node instanceof Node\Stmt\Const_) {
                    foreach ($node->consts as $const) {
                        $this->constants[] = [
                            'name' => $const->name->toString(),
                            'value' => $const->value,
                        ];
                    }
                }
                return null;
            }

            private function extractClass(Node\Stmt\Class_ $node): array
            {
                return [
                    'name' => $node->name->toString(),
                    'methods' => array_map(
                        fn($method) => $this->extractMethod($method),
                        array_filter($node->stmts, fn($stmt) => $stmt instanceof Node\Stmt\ClassMethod)
                    ),
                    'properties' => array_map(
                        fn($prop) => $this->extractProperty($prop),
                        array_filter($node->stmts, fn($stmt) => $stmt instanceof Node\Stmt\Property)
                    ),
                    'docComment' => $node->getDocComment()?->getText() ?? '',
                ];
            }

            private function extractMethod(Node\Stmt\ClassMethod $node): array
            {
                return [
                    'name' => $node->name->toString(),
                    'params' => array_map(
                        fn($param) => [
                            'name' => $param->var->name,
                            'type' => $param->type ? $param->type->toString() : 'mixed',
                            'default' => $param->default,
                        ],
                        $node->params
                    ),
                    'returnType' => $node->returnType ? $node->returnType->toString() : 'void',
                    'visibility' => $this->getVisibility($node),
                    'docComment' => $node->getDocComment()?->getText() ?? '',
                ];
            }

            private function extractProperty(Node\Stmt\Property $node): array
            {
                $prop = $node->props[0];
                return [
                    'name' => $prop->name->toString(),
                    'type' => $node->type ? $node->type->toString() : 'mixed',
                    'visibility' => $this->getVisibility($node),
                    'docComment' => $node->getDocComment()?->getText() ?? '',
                ];
            }

            private function extractFunction(Node\Stmt\Function_ $node): array
            {
                return [
                    'name' => $node->name->toString(),
                    'params' => array_map(
                        fn($param) => [
                            'name' => $param->var->name,
                            'type' => $param->type ? $param->type->toString() : 'mixed',
                        ],
                        $node->params
                    ),
                    'returnType' => $node->returnType ? $node->returnType->toString() : 'void',
                    'docComment' => $node->getDocComment()?->getText() ?? '',
                ];
            }

            private function getVisibility($node): string
            {
                if ($node->isPublic()) return 'public';
                if ($node->isProtected()) return 'protected';
                if ($node->isPrivate()) return 'private';
                return 'public';
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return [
            'classes' => $visitor->classes,
            'functions' => $visitor->functions,
            'constants' => $visitor->constants,
        ];
    }

    /**
     * Parse entire directory
     */
    public function parseDirectory(string $directory): array
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        $documentation = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $documentation[$file->getPathname()] = $this->parseFile($file->getPathname());
            }
        }

        return $documentation;
    }
}
