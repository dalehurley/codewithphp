# Chapter 16: Official PHP SDK

Official Anthropic PHP SDK examples that match the `anthropic-ai/sdk` API surface: typed requests, streaming, retries, and how to layer your own logging/testing hooks without relying on community-only factories or middleware.

## Examples

1. **sdk-advanced.php** - Typed requests, streaming, and retries using `Anthropic\Client`
2. **middleware.php** - Logging/metrics hooks built around the official client
3. **custom-transport.php** - Injecting a mock transport for offline usage
4. **testing.php** - Dependency-injected transport for unit tests

## Installation

```bash
composer install
cp .env.example .env
```
