<?php



namespace Miky\Bundle\MediaBundle\Form\Type;

use Miky\Bundle\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
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
     * @param Pool   $pool
     * @param string $class
     */
    public function __construct(Pool $pool, $class)
    {
        $this->pool = $pool;
        $this->class = $class;
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

        $this->pool->getProvider($options['provider'])->buildMediaType($builder);

        $builder->add(
            'unlink',
            CheckboxType::class,
            array(
                'label' => 'widget_label_unlink',
                'mapped' => false,
                'data' => false,
                'required' => false,
            )
        );
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
            ));

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
        return 'miky_media_type';
    }

}
