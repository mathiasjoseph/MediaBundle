<?php

namespace Miky\Bundle\MediaBundle\Doctrine\Entity;

/**
 * Gallery
 */
class Gallery extends \Miky\Bundle\MediaBundle\Model\Gallery
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
