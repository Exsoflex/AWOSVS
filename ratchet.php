<?php

// Make sure composer dependencies have been installed
require "ratchet/vendor/autoload.php";

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class MyChat implements Ratchet\MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(Ratchet\ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(Ratchet\ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App("localhost", 8080);
$app->route("/chat", new MyChat, array("*"));
$app->route("/echo", new Ratchet\Server\EchoServer, array("*"));
$app->run();