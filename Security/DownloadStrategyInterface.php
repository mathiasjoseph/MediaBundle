<?php



namespace Miky\Bundle\MediaBundle\Security;

use Miky\Component\Media\Model\MediaInterface;
use Symfony\Component\HttpFoundation\Request;

interface DownloadStrategyInterface
{
    /**
     * @param MediaInterface $media
     * @param Request        $request
     *
     * @return bool
     */
    public function isGranted(MediaInterface $media, Request $request);

    /**
     * @return string
     */
    public function getDescription();
}
