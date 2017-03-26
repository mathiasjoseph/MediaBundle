<?php



namespace Miky\Bundle\MediaBundle\Command;

use Miky\Bundle\MediaBundle\Provider\Pool;
use Sonata\CoreBundle\Model\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 */
abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return ManagerInterface
     */
    public function getMediaManager()
    {
        return $this->getContainer()->get('adevis.media.manager.media');
    }

    /**
     * @return Pool
     */
    public function getMediaPool()
    {
        return $this->getContainer()->get('adevis.media.pool');
    }
}
