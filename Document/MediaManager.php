<?php



namespace Miky\Bundle\MediaBundle\Document;

use Sonata\CoreBundle\Model\BaseDocumentManager;

class MediaManager extends BaseDocumentManager
{
    /**
     * {@inheritdoc}
     */
    public function save($entity, $andFlush = true)
    {
        // BC compatibility for $context parameter
        if ($andFlush && is_string($andFlush)) {
            $entity->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 == func_num_args()) {
            $entity->setProviderName(func_get_arg(2));
        }

        if ($andFlush && is_bool($andFlush)) {
            parent::save($entity, $andFlush);
        } else {
            // BC compatibility with previous signature
            parent::save($entity, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        throw new \RuntimeException('Not Implemented yet');
    }
}