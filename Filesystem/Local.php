<?php



namespace Miky\Bundle\MediaBundle\Filesystem;

use Gaufrette\Adapter\Local as BaseLocal;

class Local extends BaseLocal
{
    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
