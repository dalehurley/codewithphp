<?php

declare(strict_types=1);

function runTlsLab(string $mode): void
{
    $directory = sys_get_temp_dir() . '/php86-tls-' . bin2hex(random_bytes(8));
    if (!mkdir($directory, 0700)) {
        throw new RuntimeException('Cannot create TLS lab directory');
    }

    $server = null;
    $child = null;
    try {
        $ssl = ['crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_3_SERVER];
        $key = '';
        $earlyData = '';
        if ($mode === 'psk') {
            $key = random_bytes(32);
            $ssl['psk_server_cb'] = static fn ($stream, string $identity): ?OpenSSL\Psk =>
                $identity === 'tutorial-client' ? new OpenSSL\Psk($key) : null;
        } else {
            makeTlsLabCertificate($directory);
            $ssl['local_cert'] = $directory . '/cert.pem';
            $ssl['local_pk'] = $directory . '/key.pem';
            $ssl['session_cache'] = true;
            $ssl['session_id_context'] = 'php-tutorial';
        }

        if ($mode === 'early-data') {
            $ssl['max_early_data'] = 1024;
            $ssl['early_data_cb'] = static function ($stream, string $data) use (&$earlyData): void {
                $earlyData .= $data;
            };
        }

        $server = stream_socket_server(
            'tls://127.0.0.1:0',
            $errorNumber,
            $errorMessage,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            stream_context_create(['ssl' => $ssl]),
        );
        if ($server === false) {
            throw new RuntimeException("Cannot bind TLS listener: $errorMessage", $errorNumber);
        }

        $address = stream_socket_get_name($server, false);
        $child = proc_open(
            [PHP_BINARY, '-d', 'display_errors=stderr', __FILE__, $mode, $address, $directory],
            [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR],
            $pipes,
        );
        if (!is_resource($child)) {
            throw new RuntimeException('Cannot start TLS client');
        }
        fwrite($pipes[0], $key);
        fclose($pipes[0]);

        $connections = $mode === 'psk' ? 1 : 2;
        for ($connection = 0; $connection < $connections; $connection++) {
            $peer = stream_socket_accept($server, 5.0);
            if ($peer === false) {
                throw new RuntimeException('Could not accept TLS connection');
            }

            try {
                $message = match ($mode) {
                    'psk' => "PSK handshake succeeded\n",
                    'resume' => "Status ready\n",
                    'early-data' => 'Server received ' . strlen($earlyData) . " early bytes\n",
                };
                if (fwrite($peer, $message) !== strlen($message)) {
                    throw new RuntimeException('Could not send the TLS response');
                }
            } finally {
                fclose($peer);
            }
        }

        $exitCode = proc_close($child);
        $child = null;
        if ($exitCode !== 0) {
            throw new RuntimeException('TLS client failed');
        }
    } finally {
        if (is_resource($server)) {
            fclose($server);
        }
        if (is_resource($child)) {
            proc_terminate($child);
            proc_close($child);
        }
        foreach (glob($directory . '/*.pem') ?: [] as $file) {
            unlink($file);
        }
        rmdir($directory);
    }
}

function makeTlsLabCertificate(string $directory): void
{
    $command = [
        'openssl', 'req', '-x509', '-newkey', 'rsa:2048', '-nodes',
        '-subj', '/CN=localhost', '-days', '1',
        '-keyout', $directory . '/key.pem', '-out', $directory . '/cert.pem',
    ];
    $process = proc_open($command, [0 => STDIN, 1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start the openssl command');
    }
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException("Could not generate the lab certificate: $output");
    }
}

function openTlsLabStream(string $address, array $ssl)
{
    $stream = stream_socket_client(
        'tls://' . $address,
        $errorNumber,
        $errorMessage,
        5.0,
        STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => $ssl]),
    );
    if ($stream === false) {
        throw new RuntimeException("Could not connect: $errorMessage", $errorNumber);
    }
    stream_set_timeout($stream, 5);
    return $stream;
}

function runTlsLabClient(string $mode, string $address, string $directory): void
{
    $ssl = ['crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT];
    if ($mode === 'psk') {
        $key = stream_get_contents(STDIN);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('Missing lab PSK');
        }
        // This connection authenticates with the provisioned key and has no peer certificate.
        $ssl['verify_peer'] = false;
        $ssl['verify_peer_name'] = false;
        $ssl['psk_client_cb'] = static fn ($stream): OpenSSL\Psk =>
            new OpenSSL\Psk($key, 'tutorial-client');
        $stream = openTlsLabStream($address, $ssl);
        if (fgets($stream) !== "PSK handshake succeeded\n") {
            throw new RuntimeException('Missing PSK acknowledgement');
        }
        echo "PSK handshake succeeded\n";
        fclose($stream);
        return;
    }

    $ssl['cafile'] = $directory . '/cert.pem';
    $ssl['peer_name'] = 'localhost';
    $session = null;
    $firstOptions = $ssl + [
        'session_new_cb' => static function ($stream, OpenSSL\Session $newSession) use (&$session): void {
            $session = $newSession;
        },
    ];

    foreach ([1, 2] as $number) {
        $options = $number === 1 ? $firstOptions : $ssl + ['session_data' => $session];
        if ($number === 2 && $mode === 'early-data') {
            $options['early_data'] = 'status';
        }

        $stream = openTlsLabStream($address, $options);
        try {
            $message = fgets($stream);
            if ($message === false) {
                throw new RuntimeException('Missing TLS response');
            }
            $crypto = stream_get_meta_data($stream)['crypto'];
            printf("Connection %d resumed=%s\n", $number, $crypto['session_reused'] ? 'yes' : 'no');
            if ($mode === 'early-data') {
                echo $message;
                echo 'Client early data: ', ($crypto['early_data'] ?? 'not_sent'), PHP_EOL;
            }
        } finally {
            fclose($stream);
        }
        if ($number === 1 && $session === null) {
            throw new RuntimeException('Did not receive a session ticket');
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__ && $argc === 4) {
    runTlsLabClient($argv[1], $argv[2], $argv[3]);
}
