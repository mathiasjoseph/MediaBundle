<?php



namespace Miky\Bundle\MediaBundle\Generator;

use Miky\Bundle\MediaBundle\Model\MediaInterface;

interface GeneratorInterface
{
    /**
     * @param MediaInterface $media
     *
     * @return string
     */
    public function generatePath(MediaInterface $media);
}
