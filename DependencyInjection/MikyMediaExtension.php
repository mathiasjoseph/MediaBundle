<?php



namespace Miky\Bundle\MediaBundle\DependencyInjection;

use Miky\Bundle\CoreBundle\DependencyInjection\AbstractCoreExtension;
use Miky\Bundle\CoreBundle\Mapper\DoctrineCollector;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * MediaExtension.
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MikyMediaExtension  extends AbstractCoreExtension implements PrependExtensionInterface
{

    public function prepend(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/app'));
        $loader->load('config.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration($container->getParameter("miky_media.use_default_entities"));

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('media.xml');
        $loader->load('provider.xml');
        $loader->load('twig.xml');
        $loader->load('security.xml');
        $loader->load('extra.xml');
        $loader->load('form.xml');
        $loader->load('gaufrette.xml');

        // NEXT_MAJOR: Remove Following lines
        $amazonS3Definition = $container->getDefinition('miky.media.adapter.service.s3');
        if (method_exists($amazonS3Definition, 'setFactory')) {
            $amazonS3Definition->setFactory(array('Aws\S3\S3Client', 'factory'));
        } else {
            $amazonS3Definition->setFactoryClass('Aws\S3\S3Client');
            $amazonS3Definition->setFactoryMethod('factory');
        }

        // NEXT_MAJOR: Remove Following lines
        $openCloudDefinition = $container->getDefinition('miky.media.adapter.filesystem.opencloud.objectstore');
        if (method_exists($openCloudDefinition, 'setFactory')) {
            $openCloudDefinition->setFactory(array(new Reference('miky.media.adapter.filesystem.opencloud.connection'), 'ObjectStore'));
        } else {
            $openCloudDefinition->setFactoryService('miky.media.adapter.filesystem.opencloud.connection');
            $openCloudDefinition->setFactoryMethod('ObjectStore');
        }

        $loader->load('validators.xml');
        $loader->load('serializer.xml');


        $bundles = $container->getParameter('kernel.bundles');

        if ($this->isBundleExist("FOSRestBundle", $container)) {
            $this->setBundlesRequired(array("NelmioApiDocBundle"), $container);
            $loader->load('api_form_doctrine_orm.xml');
        }

        if ($this->isBundleExist('SonataNotificationBundle', $container)) {
            $loader->load('consumer.xml');
        }

        if ($this->isBundleExist('SonataFormatterBundle', $container)) {
            $loader->load('formatter.xml');
        }

        if ($this->isBundleExist('SonataBlockBundle', $container)) {
            $loader->load('block.xml');
        }


        if (isset($bundles['SonataSeoBundle'])) {
            $loader->load('seo_block.xml');
        }

        if (!isset($bundles['LiipImagineBundle'])) {
            $container->removeDefinition('miky.media.thumbnail.liip_imagine');
        }

        if (!array_key_exists($config['default_context'], $config['contexts'])) {
            throw new \InvalidArgumentException(sprintf('MikyMediaBundle - Invalid default context : %s, available : %s', $config['default_context'], json_encode(array_keys($config['contexts']))));
        }

        $loader->load('doctrine_orm.xml');

        $this->configureFilesystemAdapter($container, $config);
        $this->configureCdnAdapter($container, $config);

        $pool = $container->getDefinition('miky.media.pool');
        $pool->replaceArgument(0, $config['default_context']);

        // this shameless hack is done in order to have one clean configuration
        // for adding formats ....
        $pool->addMethodCall('__hack__', $config);

        $strategies = array();

        foreach ($config['contexts'] as $name => $settings) {
            $formats = array();

            foreach ($settings['formats'] as $format => $value) {
                $formats[$name.'_'.$format] = $value;
            }

            $strategies[] = $settings['download']['strategy'];
            $pool->addMethodCall('addContext', array($name, $settings['class'], $settings['providers'], $formats, $settings['download']));
        }

        $container->setParameter('miky.media.admin_format', $config['admin_format']);

        $strategies = array_unique($strategies);

        foreach ($strategies as $strategyId) {
            $pool->addMethodCall('addDownloadSecurity', array($strategyId, new Reference($strategyId)));
        }

            $this->registerDoctrineMapping($config);

        $container->setParameter('miky.media.resizer.simple.adapter.mode', $config['resizer']['simple']['mode']);
        $container->setParameter('miky.media.resizer.square.adapter.mode', $config['resizer']['square']['mode']);

        $this->configureParameterClass($container, $config);
        $this->configureExtra($container, $config);
        $this->configureBuzz($container, $config);
        $this->configureProviders($container, $config);
        $this->configureAdapters($container, $config);
        $this->configureResizers($container, $config);
        $this->configureClassesToCompile();
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureProviders(ContainerBuilder $container, array $config)
    {
        $container->getDefinition('miky.media.provider.image')
            ->replaceArgument(5, array_map('strtolower', $config['providers']['image']['allowed_extensions']))
            ->replaceArgument(6, $config['providers']['image']['allowed_mime_types'])
            ->replaceArgument(7, new Reference($config['providers']['image']['adapter']))
        ;

        $container->getDefinition('miky.media.provider.file')
            ->replaceArgument(5, $config['providers']['file']['allowed_extensions'])
            ->replaceArgument(6, $config['providers']['file']['allowed_mime_types'])
        ;

        $container->getDefinition('miky.media.provider.youtube')->replaceArgument(7, $config['providers']['youtube']['html5']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureBuzz(ContainerBuilder $container, array $config)
    {
        $container->getDefinition('miky.media.buzz.browser')
            ->replaceArgument(0, new Reference($config['buzz']['connector']));

        foreach (array(
            'miky.media.buzz.connector.curl',
            'miky.media.buzz.connector.file_get_contents',
        ) as $connector) {
            $container->getDefinition($connector)
                ->addMethodCall('setIgnoreErrors', array($config['buzz']['client']['ignore_errors']))
                ->addMethodCall('setMaxRedirects', array($config['buzz']['client']['max_redirects']))
                ->addMethodCall('setTimeout', array($config['buzz']['client']['timeout']))
                ->addMethodCall('setVerifyPeer', array($config['buzz']['client']['verify_peer']))
                ->addMethodCall('setProxy', array($config['buzz']['client']['proxy']));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureParameterClass(ContainerBuilder $container, array $config)
    {
        $container->setParameter('miky.media.admin.media.entity', $config['class']['media']);
        $container->setParameter('miky.media.admin.gallery.entity', $config['class']['gallery']);
        $container->setParameter('miky.media.admin.gallery_has_media.entity', $config['class']['gallery_has_media']);

        $container->setParameter('miky.media.media.class', $config['class']['media']);
        $container->setParameter('miky.media.gallery.class', $config['class']['gallery']);

    }

    /**
     * @param array $config
     */
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['media'], 'mapOneToMany', array(
            'fieldName' => 'galleryHasMedias',
            'targetEntity' => $config['class']['gallery_has_media'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => 'media',
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['gallery_has_media'], 'mapManyToOne', array(
            'fieldName' => 'gallery',
            'targetEntity' => $config['class']['gallery'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => 'galleryHasMedias',
            'joinColumns' => array(
                array(
                    'name' => 'gallery_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['gallery_has_media'], 'mapManyToOne', array(
            'fieldName' => 'media',
            'targetEntity' => $config['class']['media'],
            'cascade' => array(
                 'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => 'galleryHasMedias',
            'joinColumns' => array(
                array(
                    'name' => 'media_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['gallery'], 'mapOneToMany', array(
            'fieldName' => 'galleryHasMedias',
            'targetEntity' => $config['class']['gallery_has_media'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => 'gallery',
            'orphanRemoval' => true,
            'orderBy' => array(
                'position' => 'ASC',
            ),
        ));

        if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {
            $collector->addAssociation($config['class']['media'], 'mapManyToOne', array(
                'fieldName' => 'category',
                'targetEntity' => $config['class']['category'],
                'cascade' => array(
                    'persist',
                ),
                'mappedBy' => null,
                'inversedBy' => null,
                'joinColumns' => array(
                    array(
                     'name' => 'category_id',
                     'referencedColumnName' => 'id',
                     'onDelete' => 'SET NULL',
                    ),
                ),
                'orphanRemoval' => false,
            ));
        }
    }

    /**
     * Inject CDN dependency to default provider.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureCdnAdapter(ContainerBuilder $container, array $config)
    {
        // add the default configuration for the server cdn
        if ($container->hasDefinition('miky.media.cdn.server') && isset($config['cdn']['server'])) {
            $container->getDefinition('miky.media.cdn.server')
                ->replaceArgument(0, $config['cdn']['server']['path'])
            ;
        } else {
            $container->removeDefinition('miky.media.cdn.server');
        }

        if ($container->hasDefinition('miky.media.cdn.panther') && isset($config['cdn']['panther'])) {
            $container->getDefinition('miky.media.cdn.panther')
                ->replaceArgument(0, $config['cdn']['panther']['path'])
                ->replaceArgument(1, $config['cdn']['panther']['username'])
                ->replaceArgument(2, $config['cdn']['panther']['password'])
                ->replaceArgument(3, $config['cdn']['panther']['site_id'])
            ;
        } else {
            $container->removeDefinition('miky.media.cdn.panther');
        }

        if ($container->hasDefinition('miky.media.cdn.cloudfront') && isset($config['cdn']['cloudfront'])) {
            $container->getDefinition('miky.media.cdn.cloudfront')
                ->replaceArgument(0, $config['cdn']['cloudfront']['path'])
                ->replaceArgument(1, $config['cdn']['cloudfront']['key'])
                ->replaceArgument(2, $config['cdn']['cloudfront']['secret'])
                ->replaceArgument(3, $config['cdn']['cloudfront']['distribution_id'])
            ;
        } else {
            $container->removeDefinition('miky.media.cdn.cloudfront');
        }

        if ($container->hasDefinition('miky.media.cdn.fallback') && isset($config['cdn']['fallback'])) {
            $container->getDefinition('miky.media.cdn.fallback')
                ->replaceArgument(0, new Reference($config['cdn']['fallback']['master']))
                ->replaceArgument(1, new Reference($config['cdn']['fallback']['fallback']))
            ;
        } else {
            $container->removeDefinition('miky.media.cdn.fallback');
        }
    }

    /**
     * Inject filesystem dependency to default provider.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureFilesystemAdapter(ContainerBuilder $container, array $config)
    {
        // add the default configuration for the local filesystem
        if ($container->hasDefinition('miky.media.adapter.filesystem.local') && isset($config['filesystem']['local'])) {
            $container->getDefinition('miky.media.adapter.filesystem.local')
                ->addArgument($config['filesystem']['local']['directory'])
                ->addArgument($config['filesystem']['local']['create'])
            ;
        } else {
            $container->removeDefinition('miky.media.adapter.filesystem.local');
        }

        // add the default configuration for the FTP filesystem
        if ($container->hasDefinition('miky.media.adapter.filesystem.ftp') && isset($config['filesystem']['ftp'])) {
            $container->getDefinition('miky.media.adapter.filesystem.ftp')
                ->addArgument($config['filesystem']['ftp']['directory'])
                ->addArgument($config['filesystem']['ftp']['host'])
                ->addArgument(array(
                    'port' => $config['filesystem']['ftp']['port'],
                    'username' => $config['filesystem']['ftp']['username'],
                    'password' => $config['filesystem']['ftp']['password'],
                    'passive' => $config['filesystem']['ftp']['passive'],
                    'create' => $config['filesystem']['ftp']['create'],
                    'mode' => $config['filesystem']['ftp']['mode'],
                ))
            ;
        } else {
            $container->removeDefinition('miky.media.adapter.filesystem.ftp');
            $container->removeDefinition('miky.media.filesystem.ftp');
        }

        // add the default configuration for the S3 filesystem
        if ($container->hasDefinition('miky.media.adapter.filesystem.s3') && isset($config['filesystem']['s3'])) {
            $container->getDefinition('miky.media.adapter.filesystem.s3')
                ->replaceArgument(0, new Reference('miky.media.adapter.service.s3'))
                ->replaceArgument(1, $config['filesystem']['s3']['bucket'])
                ->replaceArgument(2, array('create' => $config['filesystem']['s3']['create'], 'region' => $config['filesystem']['s3']['region'], 'directory' => $config['filesystem']['s3']['directory'], 'ACL' => $config['filesystem']['s3']['acl']))
            ;

            $container->getDefinition('miky.media.metadata.amazon')
                ->addArgument(array(
                        'acl' => $config['filesystem']['s3']['acl'],
                        'storage' => $config['filesystem']['s3']['storage'],
                        'encryption' => $config['filesystem']['s3']['encryption'],
                        'meta' => $config['filesystem']['s3']['meta'],
                        'cache_control' => $config['filesystem']['s3']['cache_control'],
                ))
            ;

            if (3 === $config['filesystem']['s3']['sdk_version']) {
                $container->getDefinition('miky.media.adapter.service.s3')
                ->replaceArgument(0, array(
                    'credentials' => array(
                        'secret' => $config['filesystem']['s3']['secretKey'],
                        'key' => $config['filesystem']['s3']['accessKey'],
                    ),
                    'region' => $config['filesystem']['s3']['region'],
                    'version' => $config['filesystem']['s3']['version'],
                ))
            ;
            } else {
                $container->getDefinition('miky.media.adapter.service.s3')
                    ->replaceArgument(0, array(
                        'secret' => $config['filesystem']['s3']['secretKey'],
                        'key' => $config['filesystem']['s3']['accessKey'],
                    ))
                ;
            }
        } else {
            $container->removeDefinition('miky.media.adapter.filesystem.s3');
            $container->removeDefinition('miky.media.filesystem.s3');
        }

        if ($container->hasDefinition('miky.media.adapter.filesystem.replicate') && isset($config['filesystem']['replicate'])) {
            $container->getDefinition('miky.media.adapter.filesystem.replicate')
                ->replaceArgument(0, new Reference($config['filesystem']['replicate']['master']))
                ->replaceArgument(1, new Reference($config['filesystem']['replicate']['slave']))
            ;
        } else {
            $container->removeDefinition('miky.media.adapter.filesystem.replicate');
            $container->removeDefinition('miky.media.filesystem.replicate');
        }

        if ($container->hasDefinition('miky.media.adapter.filesystem.mogilefs') && isset($config['filesystem']['mogilefs'])) {
            $container->getDefinition('miky.media.adapter.filesystem.mogilefs')
                ->replaceArgument(0, $config['filesystem']['mogilefs']['domain'])
                ->replaceArgument(1, $config['filesystem']['mogilefs']['hosts'])
            ;
        } else {
            $container->removeDefinition('miky.media.adapter.filesystem.mogilefs');
            $container->removeDefinition('miky.media.filesystem.mogilefs');
        }

        if ($container->hasDefinition('miky.media.adapter.filesystem.opencloud') &&
            (isset($config['filesystem']['openstack']) || isset($config['filesystem']['rackspace']))) {
            if (isset($config['filesystem']['openstack'])) {
                $container->setParameter('miky.media.adapter.filesystem.opencloud.class', 'OpenCloud\OpenStack');
                $settings = 'openstack';
            } else {
                $container->setParameter('miky.media.adapter.filesystem.opencloud.class', 'OpenCloud\Rackspace');
                $settings = 'rackspace';
            }
            $container->getDefinition('miky.media.adapter.filesystem.opencloud.connection')
                ->replaceArgument(0, $config['filesystem'][$settings]['url'])
                ->replaceArgument(1, $config['filesystem'][$settings]['secret'])
                ;
            $container->getDefinition('miky.media.adapter.filesystem.opencloud')
                ->replaceArgument(1, $config['filesystem'][$settings]['containerName'])
                ->replaceArgument(2, $config['filesystem'][$settings]['create_container']);
            $container->getDefinition('miky.media.adapter.filesystem.opencloud.objectstore')
                ->replaceArgument(1, $config['filesystem'][$settings]['region']);
        } else {
            $container->removeDefinition('miky.media.adapter.filesystem.opencloud');
            $container->removeDefinition('miky.media.adapter.filesystem.opencloud.connection');
            $container->removeDefinition('miky.media.adapter.filesystem.opencloud.objectstore');
            $container->removeDefinition('miky.media.filesystem.opencloud');
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureExtra(ContainerBuilder $container, array $config)
    {
        if ($config['pixlr']['enabled']) {
            $container->getDefinition('miky.media.extra.pixlr')
                ->replaceArgument(0, $config['pixlr']['referrer'])
                ->replaceArgument(1, $config['pixlr']['secret'])
            ;
        } else {
            $container->removeDefinition('miky.media.extra.pixlr');
        }
    }

    /**
     * Add class to compile.
     */
    public function configureClassesToCompile()
    {
        $this->addClassesToCompile(array(
            'Miky\Bundle\\MediaBundle\\CDN\\CDNInterface',
            'Miky\Bundle\\MediaBundle\\CDN\\CloudFront',
            'Miky\Bundle\\MediaBundle\\CDN\\Fallback',
            'Miky\Bundle\\MediaBundle\\CDN\\PantherPortal',
            'Miky\Bundle\\MediaBundle\\CDN\\Server',
            'Miky\Bundle\\MediaBundle\\Extra\\Pixlr',
            'Miky\Bundle\\MediaBundle\\Filesystem\\Local',
            'Miky\Bundle\\MediaBundle\\Filesystem\\Replicate',
            'Miky\Bundle\\MediaBundle\\Generator\\DefaultGenerator',
            'Miky\Bundle\\MediaBundle\\Generator\\GeneratorInterface',
            'Miky\Bundle\\MediaBundle\\Metadata\\AmazonMetadataBuilder',
            'Miky\Bundle\\MediaBundle\\Metadata\\MetadataBuilderInterface',
            'Miky\Bundle\\MediaBundle\\Metadata\\NoopMetadataBuilder',
            'Miky\Bundle\\MediaBundle\\Metadata\\ProxyMetadataBuilder',
            'Miky\Component\\Media\\Model\\Gallery',
            'Miky\Component\\Media\\Model\\GalleryHasMedia',
            'Miky\Component\\Media\\Model\\GalleryHasMediaInterface',
            'Miky\Component\\Media\\Model\\GalleryInterface',
            'Miky\Bundle\\MediaBundle\\Doctrine\\GalleryManager',
            'Miky\Bundle\\MediaBundle\\Doctrine\\GalleryManagerInterface',
            'Miky\Component\\Media\\Model\\Media',
            'Miky\Component\\Media\\Model\\MediaInterface',
            'Miky\Bundle\\MediaBundle\\Doctrine\\MediaManagerInterface',
            'Miky\Bundle\\MediaBundle\\Provider\\BaseProvider',
            'Miky\Bundle\\MediaBundle\\Provider\\BaseVideoProvider',
            'Miky\Bundle\\MediaBundle\\Provider\\DailyMotionProvider',
            'Miky\Bundle\\MediaBundle\\Provider\\FileProvider',
            'Miky\Bundle\\MediaBundle\\Provider\\ImageProvider',
            'Miky\Bundle\\MediaBundle\\Provider\\MediaProviderInterface',
            'Miky\Bundle\\MediaBundle\\Provider\\Pool',
            'Miky\Bundle\\MediaBundle\\Provider\\VimeoProvider',
            'Miky\Bundle\\MediaBundle\\Provider\\YouTubeProvider',
            'Miky\Bundle\\MediaBundle\\Resizer\\ResizerInterface',
            'Miky\Bundle\\MediaBundle\\Resizer\\SimpleResizer',
            'Miky\Bundle\\MediaBundle\\Resizer\\SquareResizer',
            'Miky\Bundle\\MediaBundle\\Security\\DownloadStrategyInterface',
            'Miky\Bundle\\MediaBundle\\Security\\ForbiddenDownloadStrategy',
            'Miky\Bundle\\MediaBundle\\Security\\PublicDownloadStrategy',
            'Miky\Bundle\\MediaBundle\\Security\\RolesDownloadStrategy',
            'Miky\Bundle\\MediaBundle\\Security\\SessionDownloadStrategy',
            'Miky\Bundle\\MediaBundle\\Templating\\Helper\\MediaHelper',
            'Miky\Bundle\\MediaBundle\\Thumbnail\\ConsumerThumbnail',
            'Miky\Bundle\\MediaBundle\\Thumbnail\\FormatThumbnail',
            'Miky\Bundle\\MediaBundle\\Thumbnail\\ThumbnailInterface',
            'Miky\Bundle\\MediaBundle\\Twig\\Extension\\MediaExtension',
            'Miky\Bundle\\MediaBundle\\Twig\\Node\\MediaNode',
            'Miky\Bundle\\MediaBundle\\Twig\\Node\\PathNode',
            'Miky\Bundle\\MediaBundle\\Twig\\Node\\ThumbnailNode',
        ));
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureAdapters(ContainerBuilder $container, array $config)
    {
        foreach (array('gd', 'imagick', 'gmagick') as $adapter) {
            if ($container->hasParameter('miky.media.adapter.image.'.$adapter.'.class')) {
                $container->register('miky.media.adapter.image.'.$adapter, $container->getParameter('miky.media.adapter.image.'.$adapter.'.class'));
            }
        }
        $container->setAlias('miky.media.adapter.image.default', $config['adapters']['default']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureResizers(ContainerBuilder $container, array $config)
    {
        if ($container->hasParameter('miky.media.resizer.simple.class')) {
            $class = $container->getParameter('miky.media.resizer.simple.class');
            $definition = new Definition($class, array(
                new Reference('miky.media.adapter.image.default'),
                '%miky.media.resizer.simple.adapter.mode%',
                new Reference('miky.media.metadata.proxy'),
            ));
            $container->setDefinition('miky.media.resizer.simple', $definition);
        }

        if ($container->hasParameter('miky.media.resizer.square.class')) {
            $class = $container->getParameter('miky.media.resizer.square.class');
            $definition = new Definition($class, array(
                new Reference('miky.media.adapter.image.default'),
                '%miky.media.resizer.square.adapter.mode%',
                new Reference('miky.media.metadata.proxy'),
            ));
            $container->setDefinition('miky.media.resizer.square', $definition);
        }

        $container->setAlias('miky.media.resizer.default', $config['resizers']['default']);
    }
}
