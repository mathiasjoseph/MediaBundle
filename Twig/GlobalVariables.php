<?php



namespace Miky\Bundle\MediaBundle\Twig;

use Miky\Bundle\MediaBundle\Extra\Pixlr;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GlobalVariables.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pixlr|bool
     */
    public function getPixlr()
    {
        return $this->container->has('adevis.media.extra.pixlr') ? $this->container->get('adevis.media.extra.pixlr') : false;
    }

    /**
     * @return Pool
     */
    public function getPool()
    {
        return $this->container->get('adevis.media.pool');
    }
}
