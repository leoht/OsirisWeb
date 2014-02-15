<?php

namespace Osiris\ApiBundle\Api;

use Doctrine\ORM\EntityManager;
use Osiris\ApiBundle\Entity\Notice;
use Osiris\ApiBundle\Entity\Movie;

class NoticeProvider
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $notices;

    /**
     * Constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    public function loadFromMovie(Movie $movie)
    {
        $notices = $this->getEntityManager()
                    ->getRepository('OsirisApiBundle:Notice')
                    ->createQueryBuilder('n')
                    ->join('n.movie', 'm')
                    ->where('m.id = :movieId')
                    ->setParameter('movieId', $movie->getId())
                    ->getQuery()->getResult();

        $this->setNotices($notices);
    }

    public function findByTimecode($timecode)
    {
        foreach($this->notices as $notice) {
            // explode timecode range
            list($min, $max) = explode('-', $notice->getTimecode());

            if ($min >= $timecode && $timecode <= $max) {
                return $notice;
            }
        }

        return false;
    }

    /**
     * Gets the value of entityManager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Sets the value of entityManager.
     *
     * @param EntityManager $entityManager the entity manager
     *
     * @return self
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Gets the value of notices.
     *
     * @return array
     */
    public function getNotices()
    {
        return $this->notices;
    }

    /**
     * Sets the value of notices.
     *
     * @param array $notices the notices
     *
     * @return self
     */
    public function setNotices(array $notices)
    {
        $this->notices = $notices;

        return $this;
    }
}
