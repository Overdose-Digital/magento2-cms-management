<?php

namespace Overdose\CMSContent\Api;

interface CmsEntityConverterInterface
{
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
