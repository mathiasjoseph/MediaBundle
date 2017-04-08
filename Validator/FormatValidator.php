<?php


namespace Miky\Bundle\MediaBundle\Validator;

use Miky\Component\Media\Model\GalleryInterface;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ExecutionContextInterface as LegacyExecutionContextInterface;

class FormatValidator extends ConstraintValidator
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $formats = $this->pool->getFormatNamesByContext($value->getContext());

        if (!$value instanceof GalleryInterface) {
            // Interface compatibility, support for LegacyExecutionContextInterface can be removed when support for Symfony <2.5 is dropped
            if ($this->context instanceof LegacyExecutionContextInterface) {
                $this->context->addViolationAt('defaultFormat', 'Invalid instance, expected GalleryInterface');
            } else {
                $this->context->buildViolation('Invalid instance, expected GalleryInterface')
                   ->atPath('defaultFormat')
                   ->addViolation();
            }
        }

        if (!array_key_exists($value->getDefaultFormat(), $formats)) {
            $this->context->addViolation('invalid format');
        }
    }
}
