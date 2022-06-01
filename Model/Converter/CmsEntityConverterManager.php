<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Converter;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;

class CmsEntityConverterManager implements CmsEntityConverterManagerInterface
{
    /**
     * @var CmsEntityConverterInterface[]
     */
    private $converters = [];

    /**
     * @param array $converters
     */
    public function __construct(
        array $converters
    ) {
        $this->converters = $converters;
    }

    /**
     * @inheritdoc
     */
    public function getConverter(string $type): CmsEntityConverterInterface
    {
        if (isset($this->converters[$type])) {
            return $this->converters[$type];
        }
        throw new LocalizedException(__("Can't find converter"));
    }
}
