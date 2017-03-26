<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Miky\Bundle\MediaBundle\Generator;

use Miky\Bundle\MediaBundle\Model\MediaInterface;

class DefaultGenerator implements GeneratorInterface
{
    /**
     * @var int
     */
    protected $firstLevel;

    /**
     * @var int
     */
    protected $secondLevel;

    /**
     * @param int $firstLevel
     * @param int $secondLevel
     */
    public function __construct($firstLevel = 100000, $secondLevel = 1000)
    {
        $this->firstLevel = $firstLevel;
        $this->secondLevel = $secondLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePath(MediaInterface $media)
    {
        return sprintf('%s/%04s/%02s/%05s', $media->getContext(), $media->getCreatedAt()->format('Y'), $media->getCreatedAt()->format('m'), $media->getCreatedAt()->format('d'));
    }
}
