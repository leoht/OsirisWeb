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
    protected $ongoingAssociations;

    /**
     * @var Association[]
     */
    protected $completedAssociations;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->ongoingAssociations = array();
        $this->completedAssociations = array();
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

            $association = Association::createFromMessage($from, $message);

            $identifier = $association->getFacebookId() ?: $association->getAssociationCode();

            $this->ongoingAssociations[$identifier] = $association;

        } elseif ($message->getName() == MessageTypes::ASSOCIATE_WITH_FACEBOOK 
            || $message->getName() == MessageTypes::ASSOCIATE_WITH_CODE ) {

            $identifier = $message->get('facebook_id') ?: $message->get('code');

            $association = $this->ongoingAssociations[$identifier];

            if ($association->completeWithMessage($from, $message)) {
                $this->registerAssociation($association);
            } else {
                $from->send(json_encode(array(
                    'direction' => MessageTypes::FROM_PLAYER_TO_DEVICE,
                    'name'      => MessageTypes::ASSOCIATION_REFUSED,
                )));
            }
        } else {
            // other messages
        }
    }

    protected function registerAssociation(Association $association)
    {
        $associationToken = Association::createToken($association);

        $this->completedAssociations[$associationToken] = $association;

        // now communicate token to the two devices

        $messageJson = json_encode(array(
            'direction' => MessageTypes::BROADCAST,
            'name' => MessageTypes::ASSOCIATED_WITH_TOKEN,
            'data' => array(
                'token' => $associationToken,
            ),
        ));

        echo "Devices {$association->getPlayerSocket()->resourceId} and {$association->getMobileSocket()->resourceId} associated with token $associationToken";

        $association->broadcast($messageJson);
    }
}
