<?php



namespace Miky\Bundle\MediaBundle\Thumbnail;

use Miky\Bundle\MediaBundle\Model\MediaInterface;
use Miky\Bundle\MediaBundle\Provider\MediaProviderInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerThumbnail implements ThumbnailInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var ThumbnailInterface
     */
    protected $thumbnail;

    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param string                   $id
     * @param ThumbnailInterface       $thumbnail
     * @param BackendInterface         $backend
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct($id, ThumbnailInterface $thumbnail, BackendInterface $backend, EventDispatcherInterface $dispatcher = null)
    {
        $this->id = $id;
        $this->thumbnail = $thumbnail;
        $this->backend = $backend;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        $backend = $this->backend;
        $id = $this->id;

        $publish = function () use ($backend, $media, $id) {
            $backend->createAndPublish('miky.media.create_thumbnail', array(
                'thumbnailId' => $id,
                'mediaId' => $media->getId(),

                // force this value as the message is sent inside a transaction,
                // so we have a race condition
                'providerReference' => $media->getProviderReference(),
            ));
        };

        // BC compatibility for missing EventDispatcher
        if (null === $this->dispatcher) {
            @trigger_error('Since version 2.3.3, passing an empty parameter in argument 4 for __construct() in '.__CLASS__.' is deprecated and the workaround for it will be removed in 3.0.', E_USER_DEPRECATED);
            $publish();
        } else {
            $this->dispatcher->addListener('kernel.finish_request', $publish);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        return $this->thumbnail->delete($provider, $media, $formats);
    }
}
