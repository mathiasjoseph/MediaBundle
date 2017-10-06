<?php
/**
 * Created by PhpStorm.
 * User: miky
 * Date: 12/04/17
 * Time: 22:02
 */

namespace Miky\Bundle\MediaBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class MediaProperty extends Annotation
{
    /**
     * @var boolean
     */
    public $multiple;

}