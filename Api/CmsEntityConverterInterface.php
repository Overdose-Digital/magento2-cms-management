<?php


namespace Overdose\CMSContent\Api;


interface CmsEntityConverterInterface
{
    public function getCmsEntityType(): string;

    public function convertToArray(array $cmsEntities): array;
}
