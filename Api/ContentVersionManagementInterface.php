<?php

namespace Overdose\CMSContent\Api;

interface ContentVersionManagementInterface
{
    const DEFAULT_VERSION = '1.0.0';
    const XML_FILE_HEADER_LENGTH = 150;

    /**
     * Process update for all changed CMSContent records
     */
    public function processAll();

    /**
     * Update blocks CMSContent records
     *
     * @param array $ids
     * @return $this
     */
    public function processBlocks($ids = []);

    /**
     * Update pages CMSContent records
     *
     * @param array $ids
     * @return $this
     */
    public function processPages($ids = []);

    /**
     * Process update for all CMSContent records from file
     *
     * @param string $filePath
     * @return int - count of processed entities
     */
    public function processFile(string $filePath);

    /**
     * Update data for selected CMSContent record
     *
     * @param $contentVersion
     * @param $configItem
     * @return $this
     */
    public function updateVersion($contentVersion, $configItem);

    /**
     * Create CMSContent record
     *
     * @param $type
     * @param $data
     * @return $this
     */
    public function createVersion($type, $data);

    /**
     * Retrieves current version number based on identifier and type (0 - blocks, 1-pages)
     *
     * @param string $id
     * @param int $type
     * @param string|null $storeIds
     * @return string
     */
    public function getCurrentVersion(string $id, int $type, ?string $storeIds);

    /**
     * Delete content version model based on identifier and type (0 - blocks, 1-pages)
     *
     * @param int $type
     * @param string $id
     * @return bool
     */
    public function deleteContentVersion(string $id, int $type, array $storeIds);
}
