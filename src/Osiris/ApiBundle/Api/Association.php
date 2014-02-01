<?php

namespace Osiris\ApiBundle\Api;

use Ratchet\ConnectionInterface;

/**
* A device association.
*/
class Association
{
	/**
	 * @var ConnectionInterface
	 */
	protected $playerSocket;

	/**
	 * @var ConnectionInterface
	 */
	protected $mobileSocket;

	/**
	 * @var string
	 */
	protected $associationCode;

	/**
	 * @var string
	 */
	protected $facebookId;

	/**
	 * @var Boolean
	 */
	protected $completed;

	public static function createFromMessage(ConnectionInterface $from, Message $message)
	{
		$association = new static();
		$association->setCompleted(false);
		$association->setPlayerSocket($from);

		if ($message->getName() == MessageTypes::BEGIN_FACEBOOK_ASSOCIATION) {
			$association->setFacebookId($message->get('facebook_id'));

			echo "Client {$from->resourceId} initiated association with : FacebookID [{$association->getFacebookId()}]. Waiting for second device...\n";
			// ...
		} elseif ($message->getName() == MessageTypes::BEGIN_CODE_ASSOCIATION) {
			$association->setAssociationCode(Association::generateCode());

			echo "Client {$from->resourceId} initiated association with : code [{$association->getAssociationCode()}]. Waiting for second device...\n";
		}

		return $association;
	}

	public static function generateCode()
	{
		$alphabet = array('0','1','2','3','4','5','6','7','8','9');

		$code = '';

		for ($i = 0 ; $i <= 4 ; $i++) {
			$code .= $alphabet[mt_rand(0,9)];
		}

		return $code;
	}

	public function completeWithMessage(ConnectionInterface $from, Message $message)
	{
		if ($message->getName() == MessageTypes::ASSOCIATE_WITH_FACEBOOK) {
			$givenFacebookId = $message->get('facebook_id');
			
			if ($givenFacebookId == $this->getFacebookId()) {
				$this->setCompleted(true);
			}

		} elseif ($message->getName() == MessageTypes::ASSOCIATE_WITH_CODE) {
			$givenCode = $message->get('code');

			if ($givenCode == $this->getAssociationCode()) {
				$this->setCompleted(true);
			}
		}

		if ($this->getCompleted()) {
			$this->setMobileSocket($from);

			echo "Now associated with client {$from->resourceId}\n";

			return true;
		} else {
			return false;
		}
	}

    /**
     * Gets the value of playerSocket.
     *
     * @return ConnectionInterface
     */
    public function getPlayerSocket()
    {
        return $this->playerSocket;
    }

    /**
     * Sets the value of playerSocket.
     *
     * @param ConnectionInterface $playerSocket the player socket
     *
     * @return self
     */
    public function setPlayerSocket(ConnectionInterface $playerSocket)
    {
        $this->playerSocket = $playerSocket;

        return $this;
    }

    /**
     * Gets the value of mobileSocket.
     *
     * @return ConnectionInterface
     */
    public function getMobileSocket()
    {
        return $this->mobileSocket;
    }

    /**
     * Sets the value of mobileSocket.
     *
     * @param ConnectionInterface $mobileSocket the mobile socket
     *
     * @return self
     */
    public function setMobileSocket(ConnectionInterface $mobileSocket)
    {
        $this->mobileSocket = $mobileSocket;

        return $this;
    }

    /**
     * Gets the value of associationCode.
     *
     * @return string
     */
    public function getAssociationCode()
    {
        return $this->associationCode;
    }

    /**
     * Sets the value of associationCode.
     *
     * @param string $associationCode the association code
     *
     * @return self
     */
    public function setAssociationCode($associationCode)
    {
        $this->associationCode = $associationCode;

        return $this;
    }

    /**
     * Gets the value of facebookId.
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Sets the value of facebookId.
     *
     * @param string $facebookId the facebook id
     *
     * @return self
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Gets the value of completed.
     *
     * @return Boolean
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Sets the value of completed.
     *
     * @param Boolean $completed the completed
     *
     * @return self
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }
}
