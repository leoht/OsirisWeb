<?php

namespace Osiris\ApiBundle\Api;

/**
* A message with token information.
*/
class TokenizedMessage extends Message
{
	 /**
     * @var string
     */
	protected $token;

    /**
     * Gets the value of token.
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the value of token.
     *
     * @param mixed $token the token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }
}