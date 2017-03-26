<?php



namespace Miky\Bundle\MediaBundle\Metadata;

use Miky\Bundle\MediaBundle\Model\MediaInterface;

interface MetadataBuilderInterface
{
    /**
     * Get metadata for media object.
     *
     * @param MediaInterface $media
     * @param string         $filename
     *
     * @return array
     */
    public function get(MediaInterface $media, $filename);
}
