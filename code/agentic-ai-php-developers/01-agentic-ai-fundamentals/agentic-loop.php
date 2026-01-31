<?php

declare(strict_types=1);

$task = "Explain agentic AI and include today's date.";

$tools = [
    'get_date' => fn (): string => date('Y-m-d'),
];

$memory = [
    'date' => null,
    'final' => null,
    'trace' => [],
];

$maxSteps = 3;

for ($step = 1; $step <= $maxSteps; $step++) {
    $memory['trace'][] = "Step {$step}: plan next action.";

    if ($memory['date'] === null) {
        $memory['trace'][] = 'Action: call tool get_date';
        $memory['date'] = $tools['get_date']();
        $memory['trace'][] = "Observation: stored date {$memory['date']}";
        continue;
    }

    $memory['trace'][] = 'Action: compose final response';
    $memory['final'] = "Agentic AI wraps LLMs with tools, memory, and control loops."
        . " Today is {$memory['date']}.";
    break;
}

echo "Task: {$task}" . PHP_EOL;

if ($memory['final'] === null) {
    echo "No final response generated. Increase max steps." . PHP_EOL;
    exit(1);
}

echo $memory['final'] . PHP_EOL;

echo "\nTrace:" . PHP_EOL;
foreach ($memory['trace'] as $note) {
    echo "- {$note}" . PHP_EOL;
}
