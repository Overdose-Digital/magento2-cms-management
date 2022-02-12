<?php

namespace Overdose\CMSContent\Api;

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
     * @return string
     */
    public function getType(): string;

    /**
     * @param array $data
     * @return string | array
     */
    public function generate(array $data): string;
}
