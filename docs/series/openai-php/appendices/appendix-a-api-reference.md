---
title: "Appendix A: OpenAI API Reference"
description: "Complete reference guide for OpenAI API endpoints, parameters, error codes, and pricing"
series: "openai-php"
appendix: "A"
---

# Appendix A: OpenAI API Reference

Complete technical reference for all OpenAI APIs covered in this series.

## Chat Completions API

### Endpoint
```
POST https://api.openai.com/v1/chat/completions
```

### Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `model` | string | Yes | - | ID of the model to use |
| `messages` | array | Yes | - | Array of message objects |
| `temperature` | number | No | 1.0 | Sampling temperature (0-2) |
| `top_p` | number | No | 1.0 | Nucleus sampling parameter |
| `n` | integer | No | 1 | Number of completions to generate |
| `stream` | boolean | No | false | Whether to stream responses |
| `max_tokens` | integer | No | inf | Maximum tokens to generate |
| `presence_penalty` | number | No | 0.0 | Penalize new tokens based on presence (-2.0 to 2.0) |
| `frequency_penalty` | number | No | 0.0 | Penalize new tokens based on frequency (-2.0 to 2.0) |
| `logit_bias` | object | No | null | Modify likelihood of specified tokens |
| `user` | string | No | - | Unique identifier for end-user |
| `functions` | array | No | - | List of functions the model can call |
| `function_call` | string/object | No | - | Control function calling behavior |
| `response_format` | object | No | - | Format for response (e.g., JSON mode) |
| `seed` | integer | No | - | Deterministic sampling seed |
| `tools` | array | No | - | List of tools available to the model |
| `tool_choice` | string/object | No | - | Control tool calling behavior |

### Message Object

```json
{
  "role": "system|user|assistant|function|tool",
  "content": "Message content",
  "name": "Optional function/tool name",
  "function_call": { /* For assistant messages with function calls */ },
  "tool_calls": [ /* For assistant messages with tool calls */ ]
}
```

### Response Format

```json
{
  "id": "chatcmpl-123",
  "object": "chat.completion",
  "created": 1677652288,
  "model": "gpt-3.5-turbo-0125",
  "choices": [{
    "index": 0,
    "message": {
      "role": "assistant",
      "content": "Response text"
    },
    "finish_reason": "stop|length|function_call|content_filter|null"
  }],
  "usage": {
    "prompt_tokens": 9,
    "completion_tokens": 12,
    "total_tokens": 21
  }
}
```

---

## Embeddings API

### Endpoint
```
POST https://api.openai.com/v1/embeddings
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `model` | string | Yes | ID of the model to use |
| `input` | string/array | Yes | Text to embed |
| `encoding_format` | string | No | Format for embeddings (float or base64) |
| `user` | string | No | Unique identifier for end-user |

### Response Format

```json
{
  "object": "list",
  "data": [{
    "object": "embedding",
    "index": 0,
    "embedding": [0.0023064255, -0.009327292, ...]
  }],
  "model": "text-embedding-ada-002",
  "usage": {
    "prompt_tokens": 8,
    "total_tokens": 8
  }
}
```

---

## Assistants API

### Create Assistant
```
POST https://api.openai.com/v1/assistants
```

### Create Thread
```
POST https://api.openai.com/v1/threads
```

### Add Message to Thread
```
POST https://api.openai.com/v1/threads/{thread_id}/messages
```

### Run Assistant
```
POST https://api.openai.com/v1/threads/{thread_id}/runs
```

---

## Files API

### Upload File
```
POST https://api.openai.com/v1/files
```

### List Files
```
GET https://api.openai.com/v1/files
```

### Delete File
```
DELETE https://api.openai.com/v1/files/{file_id}
```

---

## Models

### Available Models

| Model | Context Window | Training Data | Best For |
|-------|---------------|---------------|----------|
| gpt-4-turbo | 128,000 tokens | Up to Apr 2023 | Complex tasks, reasoning |
| gpt-4 | 8,192 tokens | Up to Sep 2021 | High-quality responses |
| gpt-3.5-turbo | 16,385 tokens | Up to Sep 2021 | Fast, cost-effective |
| text-embedding-ada-002 | 8,191 tokens | - | Embeddings |
| text-embedding-3-small | 8,191 tokens | - | Efficient embeddings |
| text-embedding-3-large | 8,191 tokens | - | High-quality embeddings |

---

## Error Codes

| Code | Error Type | Description | Solution |
|------|-----------|-------------|----------|
| 401 | Authentication Error | Invalid API key | Verify API key is correct |
| 429 | Rate Limit Error | Too many requests | Implement retry with backoff |
| 500 | Server Error | OpenAI server error | Retry request after delay |
| 503 | Service Unavailable | OpenAI overloaded | Retry with exponential backoff |
| 400 | Invalid Request | Bad request format | Check request parameters |
| 404 | Not Found | Resource not found | Verify endpoint/resource exists |

---

## Pricing (as of 2025)

### Chat Completions

| Model | Input (per 1M tokens) | Output (per 1M tokens) |
|-------|----------------------|------------------------|
| GPT-4 Turbo | $10.00 | $30.00 |
| GPT-4 | $30.00 | $60.00 |
| GPT-3.5 Turbo | $0.50 | $1.50 |

### Embeddings

| Model | Price (per 1M tokens) |
|-------|-----------------------|
| text-embedding-3-small | $0.02 |
| text-embedding-3-large | $0.13 |
| text-embedding-ada-002 | $0.10 |

### Assistants API

Same pricing as base models, plus:
- Code Interpreter: $0.03 per session
- File Search: $0.10 per GB per day

---

## Rate Limits

Rate limits vary by account tier and model. Check current limits at:
https://platform.openai.com/account/rate-limits

### Typical Limits (Tier 1)

| Model | RPM | TPM | RPD |
|-------|-----|-----|-----|
| GPT-4 | 500 | 10,000 | - |
| GPT-3.5 Turbo | 3,500 | 90,000 | - |

RPM = Requests Per Minute
TPM = Tokens Per Minute
RPD = Requests Per Day

---

## Best Practices

1. **Always handle errors gracefully**
2. **Implement retry logic with exponential backoff**
3. **Monitor token usage for cost control**
4. **Use streaming for better UX**
5. **Cache responses when appropriate**
6. **Set max_tokens to prevent runaway costs**
7. **Use user parameter for tracking and abuse prevention**

---

**Last Updated**: 2025-11-15
**API Version**: v1

For the latest information, visit: https://platform.openai.com/docs
