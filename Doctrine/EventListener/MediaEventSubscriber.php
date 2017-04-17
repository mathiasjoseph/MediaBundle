<?php


namespace Miky\Bundle\MediaBundle\Doctrine\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Miky\Bundle\MediaBundle\Annotation\MediaProperty;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Miky\Component\Classification\Model\CategoryInterface;
use Miky\Component\Media\Model\MediaInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;


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
            Events::loadClassMetadata,
            Events::onClear,
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();
        $metadata = $eventArgs->getClassMetadata();
        $builder = new ClassMetadataBuilder($metadata);
        $reflectionClass = $metadata->getReflectionClass();
        $reflexionProperties = $reflectionClass->getProperties();
        $reader = new AnnotationReader();

        foreach ($reflexionProperties as $key => $reflexionProperty) {
            $annotation = $reader->getPropertyAnnotation($reflexionProperty, MediaProperty::class);
            if ($annotation) {
                $propertyName = $nameConverter->normalize($reflexionProperty->getName());
                $joinTableName = $nameConverter->normalize($reflectionClass->getShortName() . "_" . $reflexionProperty->getName());
                if ($annotation->multiple) {
                    $builder->createManyToMany($reflexionProperty->getName(), $this->getMediaClass())
                        ->orphanRemoval()
                        ->setJoinTable($joinTableName)
                        ->addJoinColumn("object_id", "id")
                        ->addInverseJoinColumn("media_id", "id")
                        ->cascadeAll()->build();
                } else {
                    $builder->createOneToOne($reflexionProperty->getName(), $this->getMediaClass())
                        ->orphanRemoval()
                        ->addJoinColumn($propertyName."_id", "id")
                        ->cascadeAll()->build();
                }
            }
        }
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
