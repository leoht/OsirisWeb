<?php

namespace Osiris\ApiBundle;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Osiris\ApiBundle\Api\Association;
use Osiris\ApiBundle\Api\Message;
use Osiris\ApiBundle\Api\TokenizedMessage;
use Osiris\ApiBundle\Api\MessageTypes;
use Osiris\ApiBundle\Api\NoticeProvider;

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
     * @var NoticeProvider
     */
    protected $noticeProvider;

    /**
     * Constructor.
     */
    public function __construct(NoticeProvider $noticeProvider) {
        $this->noticeProvider = $noticeProvider;
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
                unset($this->ongoingAssociations[$identifier]);
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

            if ($message->getName() === MessageTypes::REQUEST_FOR_NOTICE_AT_TIMECODE
                && '10' == $message->get('timecode')) {
                // && $notice = $this->getNoticeProvider()->findByTimecode($message->get('timecode'))) {

                   $this->completedAssociations[$message->getToken()]->broadcast(json_encode(array(
                        'direction' => MessageTypes::BROADCAST,
                        'name' => MessageTypes::NOTICE_AT_TIMECODE,
                        'data' => array(
                            'content' => 'Coucou'// $notice->getContent(),
                        ),
                    )));
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

    /**
     * Gets the value of clients.
     *
     * @return ConnectionInterface[]
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Sets the value of clients.
     *
     * @param ConnectionInterface[] $clients the clients
     *
     * @return self
     */
    public function setClients(ConnectionInterface $clients)
    {
        $this->clients = $clients;

        return $this;
    }

    /**
     * Gets the value of ongoingAssociations.
     *
     * @return Association[]
     */
    public function getOngoingAssociations()
    {
        return $this->ongoingAssociations;
    }

    /**
     * Sets the value of ongoingAssociations.
     *
     * @param Association[] $ongoingAssociations the ongoing associations
     *
     * @return self
     */
    public function setOngoingAssociations(Association $ongoingAssociations)
    {
        $this->ongoingAssociations = $ongoingAssociations;

        return $this;
    }

    /**
     * Gets the value of completedAssociations.
     *
     * @return Association[]
     */
    public function getCompletedAssociations()
    {
        return $this->completedAssociations;
    }

    /**
     * Sets the value of completedAssociations.
     *
     * @param Association[] $completedAssociations the completed associations
     *
     * @return self
     */
    public function setCompletedAssociations(Association $completedAssociations)
    {
        $this->completedAssociations = $completedAssociations;

        return $this;
    }

    /**
     * Gets the value of noticeProvider.
     *
     * @return NoticeProvider
     */
    public function getNoticeProvider()
    {
        return $this->noticeProvider;
    }

    /**
     * Sets the value of noticeProvider.
     *
     * @param NoticeProvider $noticeProvider the notice provider
     *
     * @return self
     */
    public function setNoticeProvider(NoticeProvider $noticeProvider)
    {
        $this->noticeProvider = $noticeProvider;

        return $this;
    }
}
