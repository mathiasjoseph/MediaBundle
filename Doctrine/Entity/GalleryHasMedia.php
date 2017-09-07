<?php

namespace Miky\Bundle\MediaBundle\Doctrine\Entity;

/**
 * GalleryHasMedia
 */
class GalleryHasMedia extends \Miky\Bundle\MediaBundle\Model\GalleryHasMedia
{

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
