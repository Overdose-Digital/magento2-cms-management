<?php


namespace Overdose\CMSContent\Model\Generator;


use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\CmsEntityGeneratorInterface;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;

class CmsEntityGeneratorManager implements CmsEntityGeneratorManagerInterface
{
    /**
     * @var CmsEntityGeneratorInterface[]
     */
    private $generators = [];

    public function __construct(
        array $generators
    ) {
        $this->generators = $generators;
    }

    public function getGenerator($type): CmsEntityGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if ($generator->getType() === $type) {
                return $generator;
            }
        }

        throw new LocalizedException(__("Can't find generator"));
    }
}
