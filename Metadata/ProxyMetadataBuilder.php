<?php



namespace Miky\Bundle\MediaBundle\Metadata;

use Miky\Bundle\MediaBundle\Filesystem\Replicate;
use Miky\Bundle\MediaBundle\Model\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProxyMetadataBuilder implements MetadataBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param array              $map
     */
    public function __construct(ContainerInterface $container, array $map = null)
    {
        $this->container = $container;

        if ($map !== null) {
            @trigger_error('The "map" parameter is deprecated since version 2.4 and will be removed in 3.0.', E_USER_DEPRECATED);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(MediaInterface $media, $filename)
    {
        //get adapter for current media
        if (!$this->container->has($media->getProviderName())) {
            return array();
        }

        if ($meta = $this->getAmazonBuilder($media, $filename)) {
            return $meta;
        }

        if (!$this->container->has('miky.media.metadata.noop')) {
            return array();
        }

        return $this->container->get('miky.media.metadata.noop')->get($media, $filename);
    }

    /**
     * @param MediaInterface $media
     * @param string         $filename
     *
     * @return array|bool
     */
    protected function getAmazonBuilder(MediaInterface $media, $filename)
    {
        $adapter = $this->container->get($media->getProviderName())->getFilesystem()->getAdapter();

        //handle special Replicate adapter
        if ($adapter instanceof Replicate) {
            $adapterClassNames = $adapter->getAdapterClassNames();
        } else {
            $adapterClassNames = array(get_class($adapter));
        }

        //for amazon s3
        if ((!in_array('Gaufrette\Adapter\AmazonS3', $adapterClassNames) && !in_array('Gaufrette\Adapter\AwsS3', $adapterClassNames)) || !$this->container->has('miky.media.metadata.amazon')) {
            return false;
        }

        return $this->container->get('miky.media.metadata.amazon')->get($media, $filename);
    }
}
