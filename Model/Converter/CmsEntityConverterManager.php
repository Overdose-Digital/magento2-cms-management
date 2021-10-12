<?php

namespace Overdose\CMSContent\Model\Converter;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\CmsEntityConverterInterface;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;

class CmsEntityConverterManager implements CmsEntityConverterManagerInterface
{
    private $entities = [];

    /**
     * @var CmsEntityConverterInterface[]
     */
    private $converters = [];

    public function __construct(
        array $converters
    ) {
        $this->converters = $converters;
    }

    /**
     * @param array $entities
     * @return $this|CmsEntityConverterManagerInterface
     */
    public function setEntities(array $entities): CmsEntityConverterManagerInterface
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @return CmsEntityConverterInterface
     * @throws LocalizedException
     */
    public function getConverter(): CmsEntityConverterInterface
    {
        if (count($this->entities) > 0) {
            $cmsEntity = $this->entities[0];
            foreach ($this->converters as $converter) {
                $type = $converter->getCmsEntityType();
                if ($cmsEntity instanceof $type) {
                    return $converter;
                }
            }
        }

        throw new LocalizedException(__("Can't find converter"));
    }
}
