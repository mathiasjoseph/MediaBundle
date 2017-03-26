<?php



namespace Miky\Bundle\MediaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidMediaFormat extends Constraint
{
    public $message = 'The format is not valid';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'adevis.media.validator.format';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
