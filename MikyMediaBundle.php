<?php



namespace Miky\Bundle\MediaBundle;

use Miky\Bundle\MediaBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
use Miky\Bundle\MediaBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Miky\Bundle\MediaBundle\DependencyInjection\Compiler\SecurityContextCompilerPass;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MikyMediaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddProviderCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new SecurityContextCompilerPass());

        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // this is required by the AWS SDK (see: https://github.com/knplabs/Gaufrette)
        if (!defined('AWS_CERTIFICATE_AUTHORITY')) {
            define('AWS_CERTIFICATE_AUTHORITY', true);
        }

        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping(array(
            'miky_media_type' => 'Miky\Bundle\MediaBundle\Form\Type\MediaType',
            'miky_media_api_form_media' => 'Miky\Bundle\MediaBundle\Form\Type\ApiMediaType',
            'miky_media_api_form_doctrine_media' => 'Miky\Bundle\MediaBundle\Form\Type\ApiDoctrineMediaType',
            'miky_media_api_form_gallery' => 'Miky\Bundle\MediaBundle\Form\Type\ApiGalleryType',
            'miky_media_api_form_gallery_has_media' => 'Miky\Bundle\MediaBundle\Form\Type\ApiGalleryHasMediaType',
        ));
    }
}
