<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;

interface ContentExportInterface
{
    /**
     * Create a zip file and return its name
     * @param PageInterface[] | BlockInterface[] $cmsEntities
     * @param string $type
     * @param string $fileName
     * @param bool $split
     * @return string
     */
    public function createZipFile(array $cmsEntities, string $type, string $fileName, bool $split): string;
}
