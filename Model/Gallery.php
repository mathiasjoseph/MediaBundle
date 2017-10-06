<?php

namespace Miky\Bundle\MediaBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Gallery
 */
class Gallery extends \Miky\Component\Media\Model\Gallery
{


    /**
     * {@inheritdoc}
     */
    public function __construct()
    {

        $this->galleryHasMedias = new ArrayCollection();
    }

    /**
     * Pre Persist method.
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Pre Update method.
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
