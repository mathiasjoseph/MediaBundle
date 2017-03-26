<?php



namespace Miky\Bundle\MediaBundle\Model;

interface GalleryHasMediaInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param GalleryInterface $gallery
     */
    public function setGallery(GalleryInterface $gallery = null);

    /**
     * @return GalleryInterface
     */
    public function getGallery();

    /**
     * @param MediaInterface $media
     */
    public function setMedia(MediaInterface $media = null);

    /**
     * @return MediaInterface
     */
    public function getMedia();

    /**
     * @param int $position
     *
     * @return int
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param \DateTime|null $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();
}
