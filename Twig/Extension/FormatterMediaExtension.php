<?php



namespace Miky\Bundle\MediaBundle\Twig\Extension;

use Miky\Bundle\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Miky\Bundle\MediaBundle\Twig\TokenParser\PathTokenParser;
use Miky\Bundle\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Sonata\FormatterBundle\Extension\BaseProxyExtension;

class FormatterMediaExtension extends BaseProxyExtension
{
    /**
     * @var \Twig_Extension
     */
    protected $twigExtension;

    /**
     * @param \Twig_Extension $twigExtension
     */
    public function __construct(\Twig_Extension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTags()
    {
        return array(
            'media',
            'path',
            'thumbnail',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return array(
            'Miky\Bundle\MediaBundle\Model\MediaInterface' => array(
                'getproviderreference',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new MediaTokenParser($this->getName()),
            new ThumbnailTokenParser($this->getName()),
            new PathTokenParser($this->getName()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTwigExtension()
    {
        return $this->twigExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_formatter_media';
    }

    /**
     * @param int    $media
     * @param string $format
     * @param array  $options
     *
     * @return string
     */
    public function media($media, $format, $options = array())
    {
        return $this->getTwigExtension()->media($media, $format, $options);
    }

    /**
     * @param int    $media
     * @param string $format
     * @param array  $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = array())
    {
        return $this->getTwigExtension()->thumbnail($media, $format, $options);
    }

    /**
     * @param int    $media
     * @param string $format
     *
     * @return string
     */
    public function path($media, $format)
    {
        return $this->getTwigExtension()->path($media, $format);
    }
}
