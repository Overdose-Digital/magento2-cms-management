<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Content\Generator;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;
use function Overdose\CMSContent\Model\Generator\__;

class CmsEntityGeneratorManager implements CmsEntityGeneratorManagerInterface
{
    /**
     * @var CmsEntityGeneratorInterface[]
     */
    private $generators = [];

    /**
     * @param array $generators
     */
    public function __construct(
        array $generators
    ) {
        $this->generators = $generators;
    }

    /**
     * @inheritdoc
     */
    public function getGenerator($type): CmsEntityGeneratorInterface
    {
        if (isset($this->generators[$type])) {
            return $this->generators[$type];
        }
        throw new LocalizedException(__("Can't find generator"));
    }
}
