<?php



namespace Miky\Bundle\MediaBundle\Resizer;

use Gaufrette\File;
use Imagine\Image\Box;
use Miky\Component\Media\Model\MediaInterface;

interface ResizerInterface
{
    /**
     * @param MediaInterface $media
     * @param File           $in
     * @param File           $out
     * @param string         $format
     * @param array          $settings
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings);

    /**
     * @param MediaInterface $media
     * @param array          $settings
     *
     * @return Box
     */
    public function getBox(MediaInterface $media, array $settings);
}
