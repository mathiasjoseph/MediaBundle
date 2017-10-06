<?php



namespace Miky\Bundle\MediaBundle\Form\Type;

use Miky\Bundle\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ApiMediaType.
 *
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class ApiMediaType extends AbstractType
{
    /**
     * @var Pool
     */
    protected $mediaPool;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param Pool   $mediaPool
     * @param string $class
     */
    public function __construct(Pool $mediaPool, $class)
    {
        $this->mediaPool = $mediaPool;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ProviderDataTransformer($this->mediaPool, $this->class, array(
            'empty_on_new' => false,
        )), true);

        $provider = $this->mediaPool->getProvider($options['provider_name']);
        $provider->buildMediaType($builder);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'provider_name' => 'miky.media.provider.image',
            'context' => 'api',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // NEXT_MAJOR: Return 'Miky\Bundle\MediaBundle\Form\Type\ApiDoctrineMediaType'
        // (when requirement of Symfony is >= 2.8)
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Miky\Bundle\MediaBundle\Form\Type\ApiDoctrineMediaType'
            : 'miky_media_api_form_doctrine_media';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'miky_media_api_form_media';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
