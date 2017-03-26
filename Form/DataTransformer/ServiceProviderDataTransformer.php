<?php



namespace Miky\Bundle\MediaBundle\Form\DataTransformer;

use Miky\Bundle\MediaBundle\Model\MediaInterface;
use Miky\Bundle\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ServiceProviderDataTransformer implements DataTransformerInterface
{
    /**
     * @var MediaProviderInterface
     */
    protected $provider;

    /**
     * @param MediaProviderInterface $provider
     */
    public function __construct(MediaProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
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

        $this->provider->transform($media);

        return $media;
    }
}
