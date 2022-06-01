<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Converter;

interface CmsEntityConverterInterface
{
    const CMS_MEDIA_NODE = 'media';
    const PAGE_ENTITY_CODE = 'pages';
    const BLOCK_ENTITY_CODE = 'blocks';

    /**
     * @param array $cmsEntities
     *
     * @return array
     */
    public function convertToArray(array $cmsEntities): array;
}
