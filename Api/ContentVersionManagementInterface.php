<?php

namespace Overdose\CMSContent\Api;

interface ContentVersionManagementInterface
{
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
}
