<?php

namespace Overdose\CMSContent\Model\Config;

use Magento\Framework\Config\ConverterInterface;

abstract class ConverterAbstract implements ConverterInterface
{
    protected $itemsNode = '';
    protected $childNode = '';

    /**
     * Converting data to array type
     *
     * @param mixed $source
     *
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var \DOMNodeList $items */
        $items = $source->getElementsByTagName($this->itemsNode);

        /** @var \DOMElement $item */
        foreach ($items as $item) {
            $resultArray = [];

            /** @var \DOMNodeList $children */
            $children = $item->getElementsByTagName($this->childNode);
            /** @var \DOMElement $child */
            foreach ($children as $child) {
                $childData = [];
                if (!$identifier = $child->getAttribute('identifier')) {
                    throw new \InvalidArgumentException(
                        __('Attribute "identifier" of "%1" does not exist', $this->childNode)
                    );
                }

                $childData['identifier'] = $identifier;

                /** @var \DOMNodeList $cmsAttributes */
                $cmsAttributes = $child->getElementsByTagName('attribute');
                /** \DOMElement $cmsAttribute */
                foreach ($cmsAttributes as $cmsAttribute) {
                    $childData[$cmsAttribute->getAttribute('code')] = $cmsAttribute->textContent;
                }

                $storeIdsString = empty($childData['store_ids'])
                    ? '0' : str_replace(',', '_', $childData['store_ids']);
                $childIndex = $identifier . '_' . $storeIdsString;
                $resultArray[$childIndex] = $childData;
            }

            //IF need array wrapper cms_blocks/cms_pages use $output[$this->itemsNode] = $resultArray;
            $output = array_merge($output, $resultArray);
        }

        return $output;
    }
}
