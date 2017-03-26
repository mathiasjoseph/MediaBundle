<?php



namespace Miky\Bundle\MediaBundle\Listener\ORM;

use Miky\Bundle\MediaBundle\Listener\BaseMediaEventSubscriber;
use Miky\Bundle\MediaBundle\Model\MediaInterface;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Sonata\ClassificationBundle\Model\CategoryInterface;

class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * @var CategoryInterface[]
     */
    protected $rootCategories;

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::postPersist,
            Events::onClear,
        );
    }

    public function onClear()
    {
        $this->rootCategories = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        $em = $args->getEntityManager();

        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(get_class($args->getEntity())),
            $args->getEntity()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getMedia(EventArgs $args)
    {
        $media = $args->getEntity();

        if (!$media instanceof MediaInterface) {
            return $media;
        }

        return $media;
    }

    /**
     * @param MediaInterface $media
     *
     * @return CategoryInterface
     *
     * @throws \RuntimeException
     */
    protected function getRootCategory(MediaInterface $media)
    {
        if (!$this->rootCategories) {
            $this->rootCategories = $this->container->get('sonata.classification.manager.category')->getRootCategories(false);
        }

        if (!array_key_exists($media->getContext(), $this->rootCategories)) {
            throw new \RuntimeException(sprintf('There is no main category related to context: %s', $media->getContext()));
        }

        return $this->rootCategories[$media->getContext()];
    }
}
