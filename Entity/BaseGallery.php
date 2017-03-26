<?php



namespace Miky\Bundle\MediaBundle\Entity;

use Miky\Bundle\MediaBundle\Model\Gallery;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Bundle\MediaBundle\Entity\BaseGallery.
 */
abstract class BaseGallery extends Gallery
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
