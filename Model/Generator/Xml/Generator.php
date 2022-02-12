<?php

namespace Overdose\CMSContent\Model\Generator\Xml;

use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\CmsEntityConverterInterface;
use Overdose\CMSContent\Api\CmsEntityGeneratorInterface;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;

class Generator implements CmsEntityGeneratorInterface
{
    const TYPE = 'xml';
    /**
     * @var \DOMDocument|null
     */
    private $dom = null;

    /**
     * @var \DOMDocument
     */
    private $currentDom;

    /**
     * @var ContentVersionManagementInterface
     */
    private $contentVersionManagement;

    /**
     * @param ContentVersionManagementInterface $contentVersionManagement
     */
    public function __construct(ContentVersionManagementInterface $contentVersionManagement)
    {
        $this->contentVersionManagement = $contentVersionManagement;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @param array $data
     * @return string
     * @throws \DOMException
     */
    public function generate(array $data): string
    {
        $this->init();
        $this->arrayToXml($data);

        return $this->dom->saveXML();
    }

    private function init()
    {
        $this->dom = new \DOMDocument('1.0');
        $this->dom->formatOutput = true;
        $this->currentDom = $this->dom;
    }
    /**
     * @param array $content
     * @return $this
     * @throws \DOMException
     * @throws LocalizedException
     */
    private function arrayToXml(array $content)
    {
        $root = $this->defineRoot($content);
        $this->createConfigNode($root);
        $node = $this->dom->createElement('cms_' . $root);
        $this->currentDom->appendChild($node);
        $this->setCurrentDom($node);

        foreach ($content[$root] as $key => $item) {
            $this->createEntityNode($key, $item, $root);
            $this->setCurrentDom($node);
        }

        return $this;
    }

    /**
     * @param \DOMDocument $node
     * @return $this
     */
    private function setCurrentDom($node)
    {
        $this->currentDom = $node;
        return $this;
    }

    /**
     * @param string $root_key
     * @return void
     * @throws \DOMException
     */
    private function createConfigNode(string $root)
    {
        $node = $this->dom->createElement('config');
        $node->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $node->setAttribute('xsi:noNamespaceSchemaLocation', self::XSD_TYPE_MAP[$root]);
        $this->currentDom->appendChild($node);
        $this->setCurrentDom($node);
    }

    /**
     * @param $key
     * @param $item
     * @param $root
     * @return void
     * @throws \DOMException
     */
    private function createEntityNode(string $key, array $item, string $root)
    {
        $name = $this->currentDom->nodeName;
        $node = $this->dom->createElement(substr($name, 0, -1));
        $identifier = $this->getCmsIdentifier($key);
        $node->setAttribute('identifier', $identifier);
        $this->currentDom->appendChild($node);
        $this->setCurrentDom($node);
        $this->createContentNodes($item);
        $this->setCurrentDom($node);
        $this->createStoreNodes($item);
        $this->setCurrentDom($node);
        $this->createVersionNodes($identifier, $root);
        $this->setCurrentDom($node);
    }

    /**
     * @param $_item
     * @return void
     * @throws \DOMException
     */
    private function createContentNodes($_item)
    {
        if (is_array($_item) && array_key_exists(self::MAIN_ENTITY_NODE_NAME, $_item)) {
            foreach ($_item[self::MAIN_ENTITY_NODE_NAME] as $ind => $value) {
                $node = $this->dom->createElement('attribute');
                $node->setAttribute('code', $ind);
                $node->appendChild($this->createTextNode($ind, $value));
                $this->currentDom->appendChild($node);
            }

            $this->setCurrentDom($node);
        }
    }

    /**
     * @param $_item
     * @return void
     * @throws \DOMException
     */
    private function createStoreNodes($_item)
    {
        if (is_array($_item) && array_key_exists(self::STORES_ENTITY_NODE_NAME, $_item)) {
            $stores = $_item[self::STORES_ENTITY_NODE_NAME] ?? [];
            if ($stores) {
                $stores = implode(',', array_keys($stores));
                $node = $this->dom->createElement('attribute');
                $node->setAttribute('code', 'store_ids');
                $node->appendChild($this->dom->createTextNode($stores));
                $this->currentDom->appendChild($node);
                $this->setCurrentDom($node);
            }
        }
    }

    /**
     * @param string $identifier
     * @param string $root
     * @return void
     * @throws \DOMException
     */
    private function createVersionNodes(string $identifier, string $root)
    {
        $type = ($root === CmsEntityConverterInterface::PAGE_ENTITY_CODE) ?
            ContentVersionInterface::TYPE_PAGE : ContentVersionInterface::TYPE_BLOCK;
        $node = $this->dom->createElement('attribute');
        $node->setAttribute('code', 'version');
        $node->appendChild(
            $this->dom->createTextNode($this->contentVersionManagement->getCurrentVersion($identifier, $type))
        );
        $this->currentDom->appendChild($node);
        $this->setCurrentDom($node);
    }

    /**
     * @param string $_key
     * @return string
     */
    private function getCmsIdentifier(string $_key) : string
    {
        $parts = explode(':', $_key);

        return array_pop($parts);
    }

    /**
     * @param string $ind
     * @param string|null $value
     * @return \DOMCdataSection|\DOMText|false
     */
    private function createTextNode(string $ind, ?string $value)
    {
        if ($ind === 'content') {
            return $this->dom->createCDATASection($value);
        } else {
            return $this->dom->createTextNode($value);
        }
    }

    /**
     * @param array $content
     * @return string
     * @throws LocalizedException
     */
    private function defineRoot(array $content)
    {
        if (array_key_exists(CmsEntityConverterInterface::PAGE_ENTITY_CODE, $content)) {
            return CmsEntityConverterInterface::PAGE_ENTITY_CODE;
        } elseif (array_key_exists(CmsEntityConverterInterface::BLOCK_ENTITY_CODE, $content)) {
            return CmsEntityConverterInterface::BLOCK_ENTITY_CODE;
        } else {
            throw new LocalizedException(__('Incorrect input content for xml generating!'));
        }
    }


}
