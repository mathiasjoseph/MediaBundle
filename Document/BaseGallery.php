<?php



namespace Miky\Bundle\MediaBundle\Document;

use Miky\Component\Media\Model\Gallery;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Bundle\MediaBundle\Document\BaseGallery.
 */
abstract class BaseGallery extends Gallery
{
    /**
     * Constructor.
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