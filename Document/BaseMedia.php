<?php



namespace Miky\Bundle\MediaBundle\Document;

use Miky\Bundle\MediaBundle\Model\Media;

abstract class BaseMedia extends Media
{
    /**
     * {@inheritdoc}
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
