<?php



namespace Miky\Bundle\MediaBundle\Twig\Node;

class ThumbnailNode extends \Twig_Node
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param string                $extensionName
     * @param \Twig_Node_Expression $media
     * @param \Twig_Node_Expression $format
     * @param \Twig_Node_Expression $attributes
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct($extensionName, \Twig_Node_Expression $media, \Twig_Node_Expression $format, \Twig_Node_Expression $attributes, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(array('media' => $media, 'format' => $format, 'attributes' => $attributes), array(), $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->thumbnail(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('format'))
            ->raw(', ')
            ->subcompile($this->getNode('attributes'))
            ->raw(");\n")
        ;
    }
}
