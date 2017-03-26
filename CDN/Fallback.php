<?php



namespace Miky\Bundle\MediaBundle\CDN;

class Fallback implements CDNInterface
{
    /**
     * @var CDNInterface
     */
    protected $cdn;

    /**
     * @var CDNInterface
     */
    protected $fallback;

    /**
     * @param CDNInterface $cdn
     * @param CDNInterface $fallback
     */
    public function __construct(CDNInterface $cdn, CDNInterface $fallback)
    {
        $this->cdn = $cdn;
        $this->fallback = $fallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($relativePath, $isFlushable)
    {
        if ($isFlushable) {
            return $this->fallback->getPath($relativePath, $isFlushable);
        }

        return $this->cdn->getPath($relativePath, $isFlushable);
    }

    /**
     * {@inheritdoc}
     */
    public function flushByString($string)
    {
        return $this->cdn->flushByString($string);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($string)
    {
        return $this->cdn->flush($string);
    }

    /**
     * {@inheritdoc}
     */
    public function flushPaths(array $paths)
    {
        return $this->cdn->flushPaths($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function getFlushStatus($identifier)
    {
        return $this->cdn->getFlushStatus($identifier);
    }
}
