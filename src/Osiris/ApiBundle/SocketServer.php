<?php

namespace Osiris\ApiBundle;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Osiris\ApiBundle\Api\Association;
use Osiris\ApiBundle\Api\Message;
use Osiris\ApiBundle\Api\MessageTypes;

/**
* Socket IO server.
*/
class SocketServer implements MessageComponentInterface
{
    /**
     * @var ConnectionInterface[]
     */
	protected $clients;

    /**
     * @var Association[]
     */
    protected $associations;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->associations = array();
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

        $message = Message::fromRawData($messageData);

        if ($message->getName() == MessageTypes::BEGIN_FACEBOOK_ASSOCIATION 
            || $message->getName() == MessageTypes::BEGIN_CODE_ASSOCIATION ) {

            $this->associations[] = Association::createFromMessage($from, $message);
        } elseif ($message->getName() == MessageTypes::ASSOCIATE_WITH_FACEBOOK 
            || $message->getName() == MessageTypes::ASSOCIATE_WITH_CODE ) {

            $association = $this->associations[0];
            $association->completeWithMessage($from, $message);
        } else {

        }
    }
}
