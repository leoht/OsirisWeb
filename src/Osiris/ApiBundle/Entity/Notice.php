<?php

namespace Osiris\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notice
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Notice
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=255)
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var integer
     *
     * @ORM\Column(name="timecode", type="integer")
     */
    private $timecode;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return Notice
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string 
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Notice
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set timecode
     *
     * @param integer $timecode
     * @return Notice
     */
    public function setTimecode($timecode)
    {
        $this->timecode = $timecode;

        return $this;
    }

    /**
     * Get timecode
     *
     * @return integer 
     */
    public function getTimecode()
    {
        return $this->timecode;
    }
}
