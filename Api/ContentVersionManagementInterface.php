<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Exception\InvalidXmlImportFilesException;

interface ContentVersionManagementInterface
{
    const DEFAULT_VERSION = '1.0.0';
    const XML_FILE_HEADER_LENGTH = 150;

    /**
     * Process update for all changed CMSContent records
     *
     * @return void
     */
    public function processAll(): void;

    /**
     * Update blocks CMSContent records
     *
     * @param array $ids
     *
     * @return void
     */
    public function processBlocks(array $ids = []): void;

    /**
     * Update pages CMSContent records
     *
     * @param array $ids
     *
     * @return void
     */
    public function processPages(array $ids = []): void;

    /**
     * Process update for all CMSContent records from file
     *
     * @param string $filePath
     *
     * @throws InvalidXmlImportFilesException
     * @throws LocalizedException
     * @return int - count of processed entities
     */
    public function processFile(string $filePath): int;

    /**
     * Update data for selected CMSContent record
     *
     * @param $contentVersion
     * @param $configItem
     *
     * @return ContentVersionManagementInterface
     */
    public function updateVersion($contentVersion, $configItem): ContentVersionManagementInterface;

    /**
     * Create CMSContent record
     *
     * @param int $type
     * @param array $data
     *
     * @return ContentVersionManagementInterface
     */
    public function createVersion(int $type, array $data): ContentVersionManagementInterface;

    /**
     * Retrieves current version number based on identifier and type (0 - blocks, 1-pages)
     *
     * @param string $id
     * @param int $type
     * @param string|null $storeIds
     *
     * @return string
     */
    public function getCurrentVersion(string $id, int $type, ?string $storeIds): string;

    /**
     * Delete content version model based on identifier and type (0 - blocks, 1-pages)
     *
     * @param string $id
     * @param int $type
     * @param array $storeIds
     *
     * @return mixed
     */
    public function deleteContentVersion(string $id, int $type, array $storeIds);
}
