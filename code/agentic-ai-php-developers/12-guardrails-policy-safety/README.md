# Chapter 12: Guardrails, Policy, and Safety Layers - Code Examples

This directory contains working examples demonstrating how to implement comprehensive safety layers for AI agents.

## Examples

### 01-input-sanitization.php
Demonstrates input sanitization, validation, and PII detection before processing.

**Features:**
- Pattern-based injection detection
- HTML entity encoding
- Schema validation
- PII pattern matching
- Length limits and normalization

**Run:** `php 01-input-sanitization.php`

### 02-pii-redaction.php
Shows how to detect and redact personally identifiable information (PII).

**Features:**
- Email, phone, SSN redaction
- Credit card masking (keep last 4)
- API key masking
- URL token redaction
- Custom redaction rules

**Run:** `php 02-pii-redaction.php`

### 03-output-validation.php
Validates agent outputs for safety, accuracy, and compliance.

**Features:**
- Banned content detection
- Citation requirement checking
- PII detection in outputs
- Injection attempt detection
- Output sanitization
- Scoring system

**Run:** `php 03-output-validation.php`

### 04-policy-enforcement.php
Implements organizational policy enforcement layer.

**Features:**
- Rate limiting
- Role-based access control (PII, operations)
- Business hours restrictions
- Data residency enforcement
- Approval requirements
- Priority-based evaluation

**Run:** `php 04-policy-enforcement.php`

### 05-refusal-logic.php
Implements refusal logic for high-risk and harmful requests.

**Features:**
- Violence and harm detection
- Self-harm detection (with crisis resources)
- Illegal activity detection
- Privacy violation detection
- Jailbreak attempt detection
- Risk level classification
- Contextual refusal messages

**Run:** `php 05-refusal-logic.php`

### 06-integrated-guardrails-agent.php
Complete production-ready agent with all guardrails integrated.

**Features:**
- 7-stage validation pipeline
- Input sanitization → Refusal check → PII redaction → Policy enforcement → LLM call → Output validation → Final redaction
- Comprehensive metrics tracking
- Real Claude API integration
- Structured response format

**Run:** 
```bash
export ANTHROPIC_API_KEY='your-key-here'
php 06-integrated-guardrails-agent.php
```

## Dependencies

All examples use classes from `claude-php/claude-php-agent`:
- `ClaudeAgents\Support\Validator`
- `ClaudeAgents\Support\StringHelper`
- `ClaudeAgents\Support\JsonHelper`

The integrated agent example requires:
- `claude-php/claude-php-sdk` (for API calls)
- Valid `ANTHROPIC_API_KEY` environment variable

## Installation

```bash
cd /path/to/code/agentic-ai-php-developers
composer require claude-php/claude-php-agent claude-php/claude-php-sdk
```

## Testing All Examples

Run all examples in sequence:

```bash
for file in 0{1..5}*.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Key Concepts

### Defense in Depth
Multiple layers of protection ensure safety even if one layer fails:
1. Input sanitization (remove malicious patterns)
2. Refusal logic (reject harmful requests)
3. Policy enforcement (organizational rules)
4. Output validation (safe responses)
5. PII redaction (privacy protection)

### Risk-Based Approach
Classify risks and respond appropriately:
- **CRITICAL**: Immediate refusal with crisis resources
- **HIGH**: Refusal with explanation
- **MEDIUM**: Warning with conditional approval
- **LOW**: Allow with monitoring

### Metrics and Observability
Track safety performance:
- Requests processed vs blocked
- PII instances redacted
- Policy violations
- Block rate percentage

## Production Considerations

1. **Performance**: Cache policy evaluations, use async validation
2. **Logging**: Log all guardrail actions for audit trails
3. **Tuning**: Adjust thresholds based on false positive/negative rates
4. **Updates**: Regularly update pattern lists and policies
5. **Testing**: Maintain test suites for bypass attempts

## Related Documentation

- [Chapter 12 Tutorial](/series/agentic-ai-php-developers/chapters/12-guardrails-policy-safety)
- [claude-php/claude-php-agent Documentation](https://github.com/claude-php/claude-php-agent)
- [Best Practices Guide](/series/agentic-ai-php-developers/chapters/04-agent-configuration-and-best-practices)

## License

MIT License - See main repository for details.
