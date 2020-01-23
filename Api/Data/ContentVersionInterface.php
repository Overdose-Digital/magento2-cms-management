<?php

namespace Overdose\CMSContent\Api\Data;

interface ContentVersionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const IDENTIFIER = 'identifier';
    const STORE_IDS = 'store_ids';
    const ID = 'id';
    const VERSION = 'version';
    const TYPE = 'type';
    const TYPE_BLOCK = 0;
    const TYPE_PAGE = 1;
    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
    );

    /**
     * Get type
     * @return string|null
     */
    public function getType();

    /**
     * Set type
     * @param string $type
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setType($type);

    /**
     * Get identifier
     * @return string|null
     */
    public function getIdentifier();

    /**
     * Set identifier
     * @param string $identifier
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get version
     * @return string|null
     */
    public function getVersion();

    /**
     * Set version
     * @param string $version
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setVersion($version);

    /**
     * Get store_ids
     * @return string|null
     */
    public function getStoreIds();

    /**
     * Set store_ids
     * @param string $storeIds
     * @return \Overdose\CMSContent\Api\Data\ContentVersionInterface
     */
    public function setStoreIds($storeIds);
}
