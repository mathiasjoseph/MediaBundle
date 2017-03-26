<?php



namespace Miky\Bundle\MediaBundle\Entity;

use Miky\Bundle\MediaBundle\Model\GalleryHasMedia;

abstract class BaseGalleryHasMedia extends GalleryHasMedia
{
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
