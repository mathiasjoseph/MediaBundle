<?php

namespace Miky\Bundle\MediaBundle\Entity;
use Miky\Component\Core\Model\CommonModelInterface;
use Miky\Component\Core\Model\CommonModelTrait;

use Miky\Component\Media\Model\Media as BaseMedia;

/**
 * Media
 */
class Media extends BaseMedia implements CommonModelInterface
{

    Use CommonModelTrait;
    /**
     * @var int
     */
    private $id;

    /**
     * Media constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }


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
