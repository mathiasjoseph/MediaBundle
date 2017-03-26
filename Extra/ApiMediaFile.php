<?php



namespace Miky\Bundle\MediaBundle\Extra;

use Symfony\Component\HttpFoundation\File\File;

class ApiMediaFile extends File
{
    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $mimetype;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * {@inheritdoc}
     */
    public function __construct($handle)
    {
        if (!is_resource($handle)) {
            throw new \RuntimeException('handle is not a resource');
        }

        $this->resource = $handle;

        $meta = stream_get_meta_data($handle);

        parent::__construct($meta['uri']);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return $this->extension ?: parent::getExtension();
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype()
    {
        return $this->mimetype ?: parent::getMimeType();
    }

    /**
     * @param string $mimetype
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
    }

    /**
     * {@inheritdoc}
     */
    public function guessExtension()
    {
        return $this->extension ?: parent::guessExtension();
    }
}
