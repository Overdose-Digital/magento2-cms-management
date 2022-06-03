<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Content\Generator;

use Overdose\CMSContent\Model\Content\Converter\CmsEntityConverterInterface;

interface CmsEntityGeneratorInterface
{
    const PAGE_SCHEMA_NAME = 'cms_page_data.xsd';
    const BLOCK_SCHEMA_NAME = 'cms_block_data.xsd';

    const XSD_TYPE_MAP = [
        CmsEntityConverterInterface::PAGE_ENTITY_CODE => self::PAGE_SCHEMA_NAME,
        CmsEntityConverterInterface::BLOCK_ENTITY_CODE => self::BLOCK_SCHEMA_NAME
    ];

    const MAIN_ENTITY_NODE_NAME = 'cms';
    const STORES_ENTITY_NODE_NAME = 'stores';

    /**
     * Call generate action
     *
     * @param array $data
     *
     * @return string
     */
    public function generate(array $data): string;
}
