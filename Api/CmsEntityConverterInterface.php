<?php

namespace Overdose\CMSContent\Api;

interface CmsEntityConverterInterface
{
    const CMS_MEDIA_NODE = 'media';
    const PAGE_ENTITY_CODE = 'pages';
    const BLOCK_ENTITY_CODE = 'blocks';

    /**
     * @return string
     */
    public function getCmsEntityType(): string;

    /**
     * @return string
     */
    public function getCmsEntityCode(): string;

    /**
     * @param array $cmsEntities
     * @return array
     */
    public function convertToArray(array $cmsEntities): array;
}
