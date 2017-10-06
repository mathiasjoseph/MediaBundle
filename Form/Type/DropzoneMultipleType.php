<?php

namespace Miky\Bundle\MediaBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Miky\Bundle\MediaBundle\Doctrine\MediaManager;
use Miky\Bundle\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Miky\Bundle\MediaBundle\Model\Media;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class DropzoneMultipleType extends AbstractType
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @param Pool   $pool
     */
    public function __construct(Pool $pool, MediaManager $mediaManager, TokenGeneratorInterface $tokenGenerator)
    {
        $this->pool = $pool;
        $this->tokenGenerator = $tokenGenerator;
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->addModelTransformer(new ProviderDataTransformer($this->pool, $this->class, array(
//            'provider' => $options['provider'],
//            'context' => $options['context'],
//            'empty_on_new' => $options['empty_on_new'],
//            'new_on_update' => $options['new_on_update'],
//        )));

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {

                $token = $event->getForm()->get("mediaToken")->getData();
                $medias = $this->mediaManager->findByToken($token);
                if($medias != null){
                    if ($event->getData() == null){
                        $event->setData(new ArrayCollection());
                    }
                    /** @var Media $media */
                    foreach ($medias as $media){
                        $media->setTemporary(false);
                        $media->setExpiresAt(null);
                        $media->setToken(null);
                        $event->getData()->add($media);
                    }
                }
        });



        $builder->add(
            'mediaToken', HiddenType::class, array(
                'label' => 'media_token',
                'mapped' => false,
                'data' => $this->tokenGenerator->generateToken()
            )
        )
 ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['provider'] = $options['provider'];
        $view->vars['context'] = $options['context'];
        $view->vars['max_files'] = $options['max_files'];
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'empty_on_new' => true,
                'new_on_update' => true,
                'translation_domain' => 'MikyMediaBundle',
                "max_files" => 3
            ))

            ->setRequired(array(
                'provider',
                'context',
            ))
            ->setAllowedValues("provider", array("miky.media.provider.image","miky.media.provider.file"));

        $resolver
                ->setAllowedTypes('provider', 'string')
            ->setAllowedTypes('max_files', 'integer')
                ->setAllowedTypes('context', 'string')
                ->setAllowedValues('provider', $this->pool->getProviderList())
                ->setAllowedValues('context', array_keys($this->pool->getContexts()))
            ;

    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'miky_dropzone_multiple';
    }

}
