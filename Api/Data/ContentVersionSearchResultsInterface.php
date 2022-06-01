<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ContentVersionSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get content_version list.
     *
     * @return ContentVersionInterface[]
     */
    public function getItems(): array;

    /**
     * Get content_version list as array with identifiers as keys
     *
     * @return ContentVersionInterface[]
     */
    public function getItemsArray(): array;

    /**
     * Set id list.
     *
     * @param ContentVersionInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items): ContentVersionSearchResultsInterface;
}
