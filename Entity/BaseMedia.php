<?php



namespace Miky\Bundle\MediaBundle\Entity;


abstract class BaseMedia extends \Miky\Component\Media\Model\Media
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
