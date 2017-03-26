<?php

namespace Miky\Bundle\MediaBundle\Entity;

/**
 * GalleryHasMedia
 */
class GalleryHasMedia extends BaseGalleryHasMedia
{
    /**
     * @var int
     */
    private $id;


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
