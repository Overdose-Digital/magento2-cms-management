<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Data;

use Magento\Framework\Api\SearchResults;
use Overdose\CMSContent\Api\Data\ContentVersionSearchResultsInterface;

class ContentVersionSearchResults extends SearchResults implements ContentVersionSearchResultsInterface
{
    /**
     * @inheritDoc
     */
    public function getItems(): array
    {
        return $this->_get(self::KEY_ITEMS) === null ? [] : $this->_get(self::KEY_ITEMS);
    }

    /**
     * @inheritDoc
     */
    public function setItems(array $items): ContentVersionSearchResultsInterface
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * @inheritDoc
     */
    public function getItemsArray(): array
    {
        $result = [];
        $items = $this->getItems();

        /** @var ContentVersion $item */
        foreach ($items as $item) {
            $storePart = str_replace(',', '_', $item->getStoreIds());
            $key = $item->getIdentifier() . '_' . $storePart;
            $result[$key] = $item;
        }

        return $result;
    }
}
