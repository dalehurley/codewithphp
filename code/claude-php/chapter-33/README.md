# Chapter 33: Multi-Agent Systems Code Samples

This directory contains working code samples for building multi-agent systems with Claude using the PHP SDK.

## Installation

1. Install dependencies:
```bash
composer install
```

2. Set up environment variables:
```bash
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
```

## Code Structure

```
src/MultiAgent/
├── Agent.php                    # Base agent class with Claude integration
├── DataStructures.php          # Task, TaskResult, and Message classes
├── MessageBroker.php           # Inter-agent communication system
├── AgentOrchestrator.php       # System initialization and coordination
└── Agents/
    ├── SupervisorAgent.php     # Task coordination and delegation
    ├── ResearchAgent.php       # Information gathering specialist
    ├── CodeAgent.php          # Software development specialist
    └── WriterAgent.php        # Content creation specialist
```

## Running the Demo

Execute the multi-agent demonstration:

```bash
php examples/multi-agent-demo.php
```

This will:
- Initialize a supervisor agent and three specialized workers
- Process complex tasks through the multi-agent system
- Show real-time collaboration between agents
- Display results and performance metrics

## Key Features Demonstrated

- **Agent Specialization**: Each agent has unique capabilities and expertise
- **Task Delegation**: Supervisor breaks complex tasks into subtasks
- **Inter-Agent Communication**: Message broker handles all communication
- **Tool Use**: Agents can delegate tasks and request information
- **Result Synthesis**: Supervisor combines outputs from multiple agents
- **Error Handling**: Robust error handling and retry mechanisms

## Production Considerations

For production deployment, consider:

- **Async Processing**: Replace synchronous waits with Laravel queues
- **Monitoring**: Add observability with metrics and logging
- **Security**: Implement secure inter-agent communication
- **Scaling**: Use distributed message brokers (Redis, etc.)
- **Cost Optimization**: Monitor token usage and implement caching

## SDK Compatibility

This code is compatible with:
- **PHP**: 8.2+
- **Claude-PHP-SDK**: ^0.2
- **Laravel**: 10+ (for queue integration examples)

## API Usage

The code demonstrates proper Claude API usage:

```php
use ClaudePhp\ClaudePhp;

$claude = new ClaudePhp([
    'apiKey' => $_ENV['ANTHROPIC_API_KEY']
]);

$response = $claude->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 4096,
    'messages' => [...],
    'tools' => [...]
]);
```

## Troubleshooting

### Common Issues

1. **API Key Missing**: Ensure `ANTHROPIC_API_KEY` environment variable is set
2. **Timeout Errors**: Complex tasks may take time; increase timeout values
3. **Memory Issues**: Large conversation histories can consume memory
4. **Rate Limits**: Monitor API usage to avoid hitting rate limits

### Debug Mode

Enable debug output by setting:

```php
$_ENV['CLAUDE_DEBUG'] = 'true';
```

## Related Chapters

- **Chapter 11-15**: Tool use fundamentals required for agent capabilities
- **Chapter 19**: Queue processing for async agent tasks
- **Chapter 34**: Prompt chaining for sequential workflows
- **Chapter 36**: Security best practices for production
- **Chapter 37**: Monitoring and observability patterns

## License

See the main repository LICENSE file for licensing information.