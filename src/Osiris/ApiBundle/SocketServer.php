<?php

namespace Osiris\ApiBundle;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Osiris\ApiBundle\Api\ApiMessages;

/**
* Socket IO server.
*/
class SocketServer implements MessageComponentInterface
{
	protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo sprintf("[%s] New socket client connected ({$conn->resourceId})\n", date('Y-m-d H:i:s'));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->processMessage($from, $msg);
    }

    public function onClose(ConnectionInterface $conn) {
    	$this->clients->detach($conn);

       echo sprintf("[%s] Client disconnected ({$conn->resourceId})\n", date('Y-m-d H:i:s'));
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    protected function processMessage(ConnectionInterface $from, $message)
    {
    	$messageData = json_decode($message, true);

    	// If a token isn't provided in the message, we can't process it.
    	if (!array_key_exists('token', $messageData)) {
    		$from->send(json_encode(array(
    			'code' => '400',
    			'message' => 'No token was provided.',
    		)));

    		return;
    	}

    	// If no direction is provided, we assume it's player to device
    	if (!array_key_exists('direction', $messageData)) {
    		$messageData['direction'] = ApiMessages::FROM_PLAYER_TO_DEVICE;
    	}

    	$this->dispatchMessage($from, $messageData);
    }

    protected function dispatchMessage(ConnectionInterface $from, $messageData)
    {
    	if ($messageData['direction'] == ApiMessages::FROM_PLAYER_TO_DEVICE) {
    		$this->deviceApi->handle($messageData);
    	} else if ($messageData['direction'] == ApiMessages::FROM_DEVICE_TO_PLAYER) {
    		$this->playerApi->handle($messageData);
    	} else {
    		$from->send(json_encode(array(
    			'code' => '400',
    			'message' => 'No valid direction was provided for the incoming message.',
    		)));
    	}
    }
}
