# Chapter 08: Temperature and Sampling Parameters

Code examples for Chapter 08 of the Claude for PHP Developers series.

## Overview

This chapter explores temperature, top_p, and top_k parameters that control Claude's creativity and determinism. Learn when to use deterministic vs creative outputs for different use cases.

## Installation

```bash
composer install
cp .env.example .env
# Edit .env with your ANTHROPIC_API_KEY
```

## Examples

### Core Concepts
- `examples/01-understanding-sampling.php` - Token prediction fundamentals
- `examples/02-temperature-comparison.php` - Temperature parameter comparison
- `examples/03-temperature-scale.php` - Temperature guide and recommendations

### Sampling Parameters
- `examples/04-top-p-visualization.php` - Top-p (nucleus sampling) demonstration
- `examples/05-top-p-comparison.php` - Top-p in practice
- `examples/06-top-k-demonstration.php` - Top-k sampling
- `examples/07-top-k-usage.php` - Top-k usage patterns

### Practical Applications
- `examples/08-parameter-combinations.php` - Combining temperature + top-p
- `examples/09-deterministic-extraction.php` - Data extraction with temperature 0.0
- `examples/10-creative-generation.php` - Content generation with high temperature
- `examples/11-focused-code-generation.php` - Code generation with focused settings

### Advanced Techniques
- `examples/12-adaptive-temperature.php` - Context-aware temperature adjustment
- `examples/13-consistency-tester.php` - Measuring output consistency
- `examples/14-stop-sequences-sampling.php` - Stop sequences with sampling parameters
- `examples/15-ab-testing-sampling.php` - Systematic parameter testing

### Model-Specific & Cost Analysis
- `examples/16-model-specific-parameters.php` - Model-specific optimal settings
- `examples/17-cost-implications.php` - Cost analysis of sampling strategies

## Classes

- `src/SamplingConfigManager.php` - Configuration management for sampling parameters
- `src/TemperatureGuide.php` - Temperature recommendations for different use cases
- `src/SamplingPresets.php` - Predefined sampling strategies
- `src/DataExtractor.php` - Deterministic data extraction
- `src/CreativeWriter.php` - Creative content generation
- `src/CodeGenerator.php` - Focused code generation
- `src/AdaptiveAssistant.php` - Context-aware parameter adjustment
- `src/ConsistencyTester.php` - Output consistency measurement
- `src/SamplingABTester.php` - A/B testing framework
- `src/SamplingCostAnalyzer.php` - Cost impact analysis
- `src/ModelSpecificSampling.php` - Model-specific guidelines

## Prerequisites

- PHP 8.2+
- Claude API key
- Understanding of basic Claude API usage (Chapters 00-03)
- Basic probability concepts

## Running Examples

```bash
# Basic temperature comparison
php examples/02-temperature-comparison.php

# Test consistency at different temperatures
php examples/13-consistency-tester.php

# A/B test different sampling configurations
php examples/15-ab-testing-sampling.php
```

## Key Concepts

- **Temperature**: Controls creativity vs determinism (0.0 = deterministic, 2.0 = creative)
- **Top-p**: Nucleus sampling - considers tokens until cumulative probability threshold
- **Top-k**: Considers only the k most probable tokens
- **Deterministic tasks**: Use temperature 0.0 for data extraction, parsing
- **Creative tasks**: Use temperature 1.5+ for content generation, brainstorming
- **Cost implications**: Higher temperature often means more tokens and higher costs

## Testing

All examples include error handling and are designed to run independently. Check the console output for results and any error messages.

