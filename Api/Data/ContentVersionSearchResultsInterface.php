<?php

namespace Overdose\CMSContent\Api\Data;

interface ContentVersionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get content_version list.
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface[]
     */
    public function getItems();

    /**
     * Get content_version list as array with identifiers as keys
     *
     * @return mixed
     */
    public function getItemsArray();

    /**
     * Set id list.
     * @param \Overdose\CMSContent\Api\Data\ContentVersionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
