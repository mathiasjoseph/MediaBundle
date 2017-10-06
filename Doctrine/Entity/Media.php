<?php

namespace Miky\Bundle\MediaBundle\Doctrine\Entity;


/**
 * Media
 */
class Media extends \Miky\Bundle\MediaBundle\Model\Media
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
