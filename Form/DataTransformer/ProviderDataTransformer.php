<?php



namespace Miky\Bundle\MediaBundle\Form\DataTransformer;

use Miky\Bundle\MediaBundle\Provider\Pool;
use Miky\Component\Media\Model\MediaInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ProviderDataTransformer implements DataTransformerInterface
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param Pool   $pool
     * @param string $class
     * @param array  $options
     */
    public function __construct(Pool $pool, $class, array $options = array())
    {
        $this->pool = $pool;
        $this->options = $this->getOptions($options);
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return new $this->class();
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($media)
    {
        if (!$media instanceof MediaInterface) {
            return $media;
        }

        $binaryContent = $media->getBinaryContent();

        // no binary
        if (empty($binaryContent)) {
            // and no media id
            if ($media->getId() === null && $this->options['empty_on_new']) {
                return;
            } elseif ($media->getId()) {
                return $media;
            }

            $media->setProviderStatus(MediaInterface::STATUS_PENDING);
            $media->setProviderReference(MediaInterface::MISSING_BINARY_REFERENCE);

            return $media;
        }

        // create a new media to avoid erasing other media or not ...
        $newMedia = $this->options['new_on_update'] ? new $this->class() : $media;

        $newMedia->setProviderName($media->getProviderName());
        $newMedia->setContext($media->getContext());
        $newMedia->setBinaryContent($binaryContent);

        if (!$newMedia->getProviderName() && $this->options['provider']) {
            $newMedia->setProviderName($this->options['provider']);
        }

        if (!$newMedia->getContext() && $this->options['context']) {
            $newMedia->setContext($this->options['context']);
        }

        $provider = $this->pool->getProvider($newMedia->getProviderName());

        $provider->transform($newMedia);

        return $newMedia;
    }

    /**
     * Define the default options for the DataTransformer.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getOptions(array $options)
    {
        return array_merge(array(
            'provider' => false,
            'context' => false,
            'empty_on_new' => true,
            'new_on_update' => true,
        ), $options);
    }
}
