<?php

# filename: examples/naive-approach.php

// ❌ BAD: Loads entire file into memory
// $lines = file('large-file.csv'); // Fatal error: Allowed memory size exhausted

// ❌ BAD: Loads all database rows into array
// $users = $pdo->query('SELECT * FROM users')->fetchAll(); // Memory exhausted

// ❌ BAD: Builds huge array in memory
$data = [];
for ($i = 0; $i < 1000000; $i++) {
    $data[] = ['id' => $i, 'value' => rand()];
}
// Memory exhausted
