<?php

namespace Overdose\CMSContent\Model\Data;

use Overdose\CMSContent\Api\Data\ContentVersionInterface;

class ContentVersion extends \Magento\Framework\Api\AbstractExtensibleObject implements ContentVersionInterface
{
    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get type
     * @return string|null
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * Set type
     * @param string $type
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get identifier
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->_get(self::IDENTIFIER);
    }

    /**
     * Set identifier
     * @param string $identifier
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Get version
     * @return string|null
     */
    public function getVersion()
    {
        return $this->_get(self::VERSION);
    }

    /**
     * Set version
     * @param string $version
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setVersion($version)
    {
        return $this->setData(self::VERSION, $version);
    }

    /**
     * Get store_ids
     * @return string|null
     */
    public function getStoreIds()
    {
        return $this->_get(self::STORE_IDS);
    }

    /**
     * Set store_ids
     * @param string $storeIds
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }
}
