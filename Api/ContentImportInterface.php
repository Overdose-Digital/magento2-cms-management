<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

interface ContentImportInterface
{
    const OD_CMS_MODE_UPDATE = 'update';
    const OD_CMS_MODE_SKIP = 'skip';

    const OD_MEDIA_MODE_NONE = 'none';
    const OD_MEDIA_MODE_UPDATE = 'update';
    const OD_MEDIA_MODE_SKIP = 'skip';
    /**
     * Import contents from zip archive and return number of imported records (-1 on error)
     * @param string $fileName
     * @param bool $rm = true
     * @return int
     */
    public function importContentFromZipFile($fileName, $rm = false): int;

    /**
     * Set CMS mode on import
     * @param $mode
     * @return ContentImportInterface
     */
    public function setCmsModeOption($mode): ContentImportInterface;

    /**
     * Set media mode on import
     * @param $mode
     * @return ContentImportInterface
     */
    public function setMediaModeOption($mode): ContentImportInterface;

    /**
     * Set stores mapping on import
     * @param array $storesMap
     * @return ContentImportInterface
     */
    public function setStoresMapValue(array $storesMap): ContentImportInterface;
}
