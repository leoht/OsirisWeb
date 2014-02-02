<?php

namespace Osiris\ApiBundle;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Osiris\ApiBundle\Api\Association;
use Osiris\ApiBundle\Api\Message;
use Osiris\ApiBundle\Api\TokenizedMessage;
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

    /**
     * Constructor.
     */
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

    /**
     * Process a message.
     * If the two devices haven' been associated yet,
     * this is where the association process takes place.
     * Otherwise, the message is simply dispatched to the devices association,
     * which in turns takes care of delivering the message to the device.
     */
    protected function processMessage(ConnectionInterface $from, $message)
    {
    	$messageData = json_decode($message, true);
        // create the message object from the received data
        $message = Message::fromRawData($messageData);

        if ($message->getName() == MessageTypes::BEGIN_FACEBOOK_ASSOCIATION 
            || $message->getName() == MessageTypes::BEGIN_CODE_ASSOCIATION ) {

            // If it is an association initialization,
            // we temporarely register the incomplete association,
            // waiting for the second device to associate.

            $association = Association::createFromMessage($from, $message);

            $identifier = $association->getFacebookId() ?: $association->getAssociationCode();

            $this->ongoingAssociations[$identifier] = $association;

        } elseif ($message->getName() == MessageTypes::ASSOCIATE_WITH_FACEBOOK 
            || $message->getName() == MessageTypes::ASSOCIATE_WITH_CODE ) {

            // Now we have a request for association from the second device.
            // We rely on the Association::completeWithMessage method to
            // validate the association (with right code/facebook ID) and if so,
            // the association is registered as complete and the two devices can now
            // communicate.

            $identifier = $message->get('facebook_id') ?: $message->get('code');

            if (!array_key_exists($identifier, $this->ongoingAssociations)) {
                $from->send(json_encode(array(
                    'direction' => MessageTypes::FROM_PLAYER_TO_DEVICE,
                    'name'      => MessageTypes::ASSOCIATION_REFUSED,
                )));

                return;
            }

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
            
            // For all other messages, a token should be provided
            // If so, we should have at this point a TokenizedMessage instance
            // (provided by the Message::create factory method).
            // Otherwise it means that no token has been provided, and we
            // cannot do anything else without the token.

            if (!$message instanceof TokenizedMessage) {
                $from->send(json_encode(array(
                    'code' => 400,
                    'message' => 'No token information was provided.',
                )));

                return;
            }

            $this->completedAssociations[$message->getToken()]->dispatch($message);
        }
    }

    /**
     * Registers a completed association.
     * Once an association is completed, it is registered
     * using its token.
     */
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
