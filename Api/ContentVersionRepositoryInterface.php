<?php

namespace Overdose\CMSContent\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ContentVersionRepositoryInterface
{
    /**
     * Save content_version
     * @param \Overdose\CMSContent\Api\Data\ContentVersionInterface $contentVersion
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Overdose\CMSContent\Api\Data\ContentVersionInterface $contentVersion
    );

    /**
     * Retrieve content_version
     * @param string $id
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve content_version matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Overdose\CMSContent\Api\Data\ContentVersionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete content_version
     * @param \Overdose\CMSContent\Api\Data\ContentVersionInterface $contentVersion
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Overdose\CMSContent\Api\Data\ContentVersionInterface $contentVersion
    );

    /**
     * Delete content_version by ID
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
