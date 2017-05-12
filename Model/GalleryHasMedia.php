<?php

namespace Miky\Bundle\MediaBundle\Model;

/**
 * GalleryHasMedia
 */
class GalleryHasMedia extends \Miky\Component\Media\Model\GalleryHasMedia
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
