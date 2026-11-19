<?php

// filename: polling-api.php

declare(strict_types=1);

use Io\Poll\Context;
use Io\Poll\Event;
use Time\Duration;

$pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
if ($pair === false) {
    throw new RuntimeException('Cannot create local sockets');
}
[$reader, $writer] = $pair;
stream_set_blocking($reader, false);

$poll = new Context();
$watcher = $poll->add(new StreamPollHandle($reader), [Event::Read], data: 'inbox');

try {
    echo 'Initially ready: ', count($poll->wait(Duration::fromSeconds(0))), PHP_EOL;
    if (fwrite($writer, "job-ready\n") !== 10) {
        throw new RuntimeException('Cannot send the message');
    }
    foreach ($poll->wait(Duration::fromSeconds(1)) as $ready) {
        if ($ready->hasTriggered(Event::Read)) {
            printf("%s: %s", $ready->getData(), fgets($ready->getHandle()->getStream()));
        }
    }
} finally {
    $watcher->remove();
    fclose($reader);
    fclose($writer);
}
