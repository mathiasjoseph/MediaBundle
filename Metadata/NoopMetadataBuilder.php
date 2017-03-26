<?php



namespace Miky\Bundle\MediaBundle\Metadata;

use Miky\Bundle\MediaBundle\Model\MediaInterface;

class NoopMetadataBuilder implements MetadataBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(MediaInterface $media, $filename)
    {
        return array();
    }
}
