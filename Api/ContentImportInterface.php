<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Exception;

interface ContentImportInterface
{
    const OD_CMS_MODE_UPDATE = 'update';
    const OD_CMS_MODE_SKIP = 'skip';

    const OD_MEDIA_MODE_NONE = 'none';
    const OD_MEDIA_MODE_UPDATE = 'update';
    const OD_MEDIA_MODE_SKIP = 'skip';

    const MEDIA_ARCHIVE_PATH = 'media';

    /**
     * Import contents from zip archive and return number of imported records (-1 on error)
     *
     * @param string $fileName
     * @param bool $rm
     *
     * @return int
     * @throws Exception
     */
    public function importContentFromZipFile(string $fileName, bool $rm): int;

    /**
     * Set CMS mode on import
     *
     * @param string $mode
     *
     * @return ContentImportInterface
     */
    public function setCmsModeOption(string $mode): ContentImportInterface;

    /**
     * Set media mode on import
     *
     * @param string $mode
     *
     * @return ContentImportInterface
     */
    public function setMediaModeOption(string $mode): ContentImportInterface;
}
