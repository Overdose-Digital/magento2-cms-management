<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Magento\Cms\Api\Data\BlockInterface;

interface ContentInterface
{
    const OD_CMS_MODE_UPDATE = 'update';
    const OD_CMS_MODE_SKIP = 'skip';

    const OD_MEDIA_MODE_NONE = 'none';
    const OD_MEDIA_MODE_UPDATE = 'update';
    const OD_MEDIA_MODE_SKIP = 'skip';

    /**
     * Set CMS mode on import
     * @param $mode
     * @return ContentInterface
     */
    public function setCmsModeOption($mode): ContentInterface;

    /**
     * Set media mode on import
     * @param $mode
     * @return ContentInterface
     */
    public function setMediaModeOption($mode): ContentInterface;

    /**
     * Set stores mapping on import
     * @param array $storesMap
     * @return ContentInterface
     */
    public function setStoresMapValue(array $storesMap): ContentInterface;

    /**
     * Return CMS block to array
     * @param BlockInterface $blockInterface
     * @return array
     */
    public function convertBlockToArray(BlockInterface $blockInterface): array;

    /**
     * Return CMS page to array
     * @param \Magento\Cms\Api\Data\PageInterface $pageInterface
     * @return array
     */
    public function convertPageToArray(\Magento\Cms\Api\Data\PageInterface $pageInterface): array;

    /**
     * Return CMS blocks as array
     * @param BlockInterface[] $blockInterfaces
     * @return array
     */
    public function convertBlocksToArray(array $blockInterfaces): array;

    /**
     * Return CMS pages as array
     * @param \Magento\Cms\Api\Data\PageInterface[] $pageInterfaces
     * @return array
     */
    public function convertPagesToArray(array $pageInterfaces): array;

    /**
     * Create a zip file and return its name
     * @param \Magento\Cms\Api\Data\PageInterface[] $pageInterfaces
     * @param BlockInterface[] $blockInterfaces
     * @return string
     */
    public function createZipFile(array $pageInterfaces, array $blockInterfaces): string;

    /**
     * Import a single page from an array and return false on error and true on success
     * @param array $pageData
     * @return bool
     */
    public function importPageContentFromArray(array $pageData): bool;

    /**
     * Import a single block from an array and return false on error and true on success
     * @param array $blockData
     * @return bool
     */
    public function importBlockContentFromArray(array $blockData): bool;

    /**
     * Import contents from array and return number of imported records (-1 on error)
     * @param array $payload
     * @param string $archivePath = null
     * @return int
     */
    public function importContentFromArray(array $payload, $archivePath = null): int;

    /**
     * Import contents from zip archive and return number of imported records (-1 on error)
     * @param string $fileName
     * @param bool $rm = true
     * @return int
     */
    public function importContentFromZipFile($fileName, $rm = false): int;
}
