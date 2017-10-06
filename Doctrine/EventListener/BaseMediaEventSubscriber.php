<?php



namespace Miky\Bundle\MediaBundle\Doctrine\EventListener;


use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Miky\Bundle\MediaBundle\Provider\MediaProviderInterface;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Miky\Component\Media\Model\ContainsMediaInterface;
use Miky\Component\Media\Model\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseMediaEventSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pool
     */
    public function getPool()
    {
        return $this->container->get('miky.media.pool');
    }

    public function getMediaClass(){
        return $this->container->getParameter('miky.media.media.class');
    }

    /**
     * @param EventArgs $args
     */
    public function postUpdate(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postUpdate($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function postRemove(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postRemove($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function postPersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postPersist($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function preUpdate(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($this->getMedia($args));
        $provider->preUpdate($this->getMedia($args));

        $this->recomputeSingleEntityChangeSet($args);
    }

    /**
     * @param EventArgs $args
     */
    public function preRemove(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->preRemove($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function prePersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($this->getMedia($args));
        $provider->prePersist($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    abstract protected function recomputeSingleEntityChangeSet(EventArgs $args);

    /**
     * @param EventArgs $args
     *
     * @return MediaInterface
     */
    abstract protected function getMedia(EventArgs $args);

    /**
     * @param EventArgs $args
     *
     * @return MediaProviderInterface
     */
    protected function getProvider(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (!$media instanceof MediaInterface) {
            return;
        }

        return $this->getPool()->getProvider($media->getProviderName());
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @return boolean
     */
    protected function isContainsMedia(LifecycleEventArgs $args){
        if (!in_array(ContainsMediaInterface::class, class_implements(get_class($args->getEntity())))) {
            return false;
        }
        return true;
    }

}
