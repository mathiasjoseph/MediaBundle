<?php

namespace Miky\Bundle\MediaBundle\Entity;

/**
 * Gallery
 */
class Gallery extends BaseGallery
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
