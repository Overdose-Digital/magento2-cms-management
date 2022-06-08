<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;

interface ContentVersionRepositoryInterface
{
    /**
     * Save content_version
     *
     * @param ContentVersionInterface $contentVersion
     *
     * @return ContentVersionInterface
     * @throws LocalizedException
     */
    public function save(ContentVersionInterface $contentVersion): ContentVersionInterface;

    /**
     * Retrieve content_version
     *
     * @param string $id
     *
     * @return ContentVersionInterface
     * @throws LocalizedException
     */
    public function get(string $id): ContentVersionInterface;

    /**
     * Retrieve content_version matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete content_version
     *
     * @param ContentVersionInterface $contentVersion
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ContentVersionInterface $contentVersion): bool;

    /**
     * Delete content_version by ID
     *
     * @param string $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $id): bool;
}
