<?php

namespace Miky\Bundle\MediaBundle\Form\Type;

use Miky\Bundle\MediaBundle\Doctrine\MediaManager;
use Miky\Bundle\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
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

class DropzoneType extends AbstractType
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
     * @param string $class
     */
    public function __construct(Pool $pool, MediaManager $mediaManager, $class, TokenGeneratorInterface $tokenGenerator)
    {
        $this->pool = $pool;
        $this->class = $class;
        $this->tokenGenerator = $tokenGenerator;
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ProviderDataTransformer($this->pool, $this->class, array(
            'provider' => $options['provider'],
            'context' => $options['context'],
            'empty_on_new' => $options['empty_on_new'],
            'new_on_update' => $options['new_on_update'],
        )));

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
                $event->setData(null);
            }

        });
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {

                $token = $event->getForm()->get("mediaToken")->getData();
                $media = $this->mediaManager->findOneByToken($token);
                if($media != null){
                    $event->setData($media);
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
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => $this->class,
                'empty_on_new' => true,
                'new_on_update' => true,
                'translation_domain' => 'MikyMediaBundle',
            ))

            ->setRequired(array(
                'provider',
                'context',
            ))
            ->setAllowedValues("provider", array("miky.media.provider.image","miky.media.provider.file"));

        $resolver
                ->setAllowedTypes('provider', 'string')
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
        return 'miky_dropzone';
    }

}
