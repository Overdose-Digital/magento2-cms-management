<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ContentVersionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Entity Fields
     */
    const IDENTIFIER = 'identifier';
    const STORE_IDS = 'store_ids';
    const ID = 'id';
    const VERSION = 'version';
    const TYPE = 'type';
    /**#@-*/

    /**#@+
     * Types
     */
    const TYPE_BLOCK = 0;
    const TYPE_PAGE = 1;
    /**#@-*/

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ContentVersionInterface
     */
    public function setType(string $type): ContentVersionInterface;

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier(): ?string;

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return ContentVersionInterface
     */
    public function setIdentifier(string $identifier): ContentVersionInterface;

    /**
     * Get version
     *
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Set version
     *
     * @param string $version
     *
     * @return ContentVersionInterface
     */
    public function setVersion(string $version): ContentVersionInterface;

    /**
     * Get store_ids
     *
     * @return string|null
     */
    public function getStoreIds(): ?string;

    /**
     * Set store_ids
     *
     * @param string $storeIds
     *
     * @return ContentVersionInterface
     */
    public function setStoreIds(string $storeIds): ContentVersionInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
     *
     * @return ContentVersionInterface
     */
    public function setExtensionAttributes(
        \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
    ): ContentVersionInterface;

}
