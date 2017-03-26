<?php



namespace Miky\Bundle\MediaBundle\Model;

abstract class GalleryManager implements GalleryManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $class = $this->getClass();

        return new $class();
    }
}
