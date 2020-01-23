<?php

namespace Overdose\CMSContent\Model\Data;

class ContentVersionSearchResults extends \Magento\Framework\Api\SearchResults
    implements \Overdose\CMSContent\Api\Data\ContentVersionSearchResultsInterface
{
    /**
     * @inheritDoc
     */
    public function getItemsArray()
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
