<?php



namespace Miky\Bundle\MediaBundle\Generator;

use Miky\Component\Media\Model\MediaInterface;

class ODMGenerator implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generatePath(MediaInterface $media)
    {
        $id = $media->getId();

        return sprintf('%s/%04s/%02s', $media->getContext(), substr($id, 0, 4), substr($id, 4, 2));
    }
}
