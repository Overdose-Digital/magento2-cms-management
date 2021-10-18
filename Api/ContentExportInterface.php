<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

interface ContentExportInterface
{
    /**
     * Create a zip file and return its name
     * @param \Magento\Cms\Api\Data\PageInterface[] | \Magento\Cms\Api\Data\BlockInterface[] $cmsEntities
     * @param string $type
     * @param string $fileName
     * @return string
     */
    public function createZipFile(array $cmsEntities, string $type, string $fileName = null): string;
}
