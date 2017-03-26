<?php



namespace Miky\Bundle\MediaBundle\Document;

use Miky\Bundle\MediaBundle\Model\GalleryInterface;
use Miky\Bundle\MediaBundle\Model\GalleryManagerInterface;
use Sonata\CoreBundle\Model\BaseDocumentManager;

class GalleryManager extends BaseDocumentManager implements GalleryManagerInterface
{
    /**
     * BC Compatibility.
     *
     * @deprecated Please use save() from now
     *
     * @param GalleryInterface $gallery
     */
    public function update(GalleryInterface $gallery)
    {
        parent::save($gallery);
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        throw new \RuntimeException('Not Implemented yet');
    }
}
