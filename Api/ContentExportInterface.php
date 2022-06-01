<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

interface ContentExportInterface
{
    /**
     * Create a zip file and return its name
     *
     * @param array $convertedEntities
     * @param string $entityType
     * @param string $type
     * @param string $fileName
     * @param bool $split
     *
     * @return string
     */
    public function createZipFile(
        array $convertedEntities,
        string $entityType,
        string $type,
        string $fileName,
        bool $split
    ): string;
}
