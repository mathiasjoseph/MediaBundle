<?php



namespace Miky\Bundle\MediaBundle\Block;

use Miky\Bundle\CoreBundle\Model\Metadata;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PageExtension.
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FeatureMediaBlockService extends MediaBlockService
{
    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'media' => false,
            'orientation' => 'left',
            'title' => false,
            'content' => false,
            'context' => false,
            'mediaId' => null,
            'format' => false,
            'template' => 'MikyMediaBundle:Block:block_feature_media.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array(
                    'required' => false,
                    'label' => 'form.label_title',
                )),
                array('content', 'textarea', array(
                    'required' => false,
                    'label' => 'form.label_content',
                )),
                array('orientation', 'choice', array(
                    'required' => false,
                    'choices' => array(
                        'left' => 'form.label_orientation_left',
                        'right' => 'form.label_orientation_right',
                    ),
                    'label' => 'form.label_orientation',
                )),
                array($this->getMediaBuilder($formMapper), null, array()),
                array('format', 'choice', array(
                    'required' => count($formatChoices) > 0,
                    'choices' => $formatChoices,
                    'label' => 'form.label_format',
                )),
            ),
            'translation_domain' => 'MikyMediaBundle',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/sonatamedia/blocks/feature_media/theme.css',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'MikyMediaBundle', array(
            'class' => 'fa fa-picture-o',
        ));
    }
}
