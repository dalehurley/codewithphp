<?php
/**
 * PHP-Python Integration Example
 * Demonstrates calling Python scripts from PHP via proc_open
 */
declare(strict_types=1);

$data = [10, 20, 30, 40, 50];
$json_input = json_encode($data);

// Call Python script
$process = proc_open('python3 examples/process_data.py', [
    0 => ['pipe', 'r'], // stdin
    1 => ['pipe', 'w'], // stdout
    2 => ['pipe', 'w'], // stderr
], $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $json_input);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    
    $return_code = proc_close($process);

    if ($return_code !== 0) {
        echo "Error calling Python:\n{$stderr}\n";
        exit(1);
    }

    $result = json_decode($output, true);
    echo "PHP received from Python:\n";
    echo "  Mean: " . $result['mean'] . "\n";
    echo "  Sum: " . $result['sum'] . "\n";
}
