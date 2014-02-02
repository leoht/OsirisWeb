<?php

namespace Osiris\ApiBundle\Api;

/**
* A message.
*/
class Message
{
    /**
     * @var string
     */
	protected $name;

     /**
     * @var string
     */
	protected $direction;

     /**
     * @var array
     */
	protected $data;

     /**
     * Creates a message from raw data in an array.
     */
	public static function fromRawData(array $data)
	{
		$message = new static();

        // If the message contains token information,
        // it means that the two devices are already associated

        if (array_key_exists('token', $data)) {
            $message = new TokenizedMessage();
            $message->setToken($data['token']);
        }

		// We cannot load a message if required infos are not provided.
		if (!array_key_exists('direction', $data)
			|| !array_key_exists('name', $data)) {
			return;
		}

		// If no direction is provided, we assume it's from the device
    	if (!array_key_exists('direction', $data)) {
    		$data['direction'] = MessageTypes::FROM_DEVICE_TO_PLAYER;
    	}

		$message->setName($data['name']);
		$message->setDirection($data['direction']);
		if (array_key_exists('data', $data)) {
			$message->setData($data['data']);
		} else {
			$message->setData(array());
		}

		return $message;
	}

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of direction.
     *
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Sets the value of direction.
     *
     * @param mixed $direction the direction
     *
     * @return self
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Gets the value of data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the value of data.
     *
     * @param mixed $data the data
     *
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function get($key)
    {
    	$data = $this->getData();

    	if (array_key_exists($key, $data)) {
    		return $data[$key];
    	}

    	return false;
    }
}
