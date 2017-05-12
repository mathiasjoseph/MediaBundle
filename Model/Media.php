<?php

namespace Miky\Bundle\MediaBundle\Model;


/**
 * Media
 */
class Media extends \Miky\Component\Media\Model\Media
{

    /**
     * Media constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
