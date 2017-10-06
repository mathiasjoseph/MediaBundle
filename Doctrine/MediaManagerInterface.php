<?php



namespace Miky\Bundle\MediaBundle\Doctrine;

use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Model\PageableManagerInterface;

/**
 * @deprecated Use Sonata\CoreBundle\Model\ManagerInterface instead
 */
interface MediaManagerInterface extends ManagerInterface, PageableManagerInterface
{
}
