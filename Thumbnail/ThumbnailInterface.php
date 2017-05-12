<?php



namespace Miky\Bundle\MediaBundle\Thumbnail;

use Miky\Component\Media\Model\MediaInterface;
use Miky\Bundle\MediaBundle\Provider\MediaProviderInterface;

interface ThumbnailInterface
{
    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     * @param string                 $format
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format);

    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     * @param string                 $format
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format);

    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media);

    /**
     * @param MediaProviderInterface $provider
     * @param MediaInterface         $media
     * @param string|array           $formats
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null);
}
