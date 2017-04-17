<?php



namespace Miky\Bundle\MediaBundle\Entity;


abstract class BaseGalleryHasMedia extends \Miky\Component\Media\Model\GalleryHasMedia
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
