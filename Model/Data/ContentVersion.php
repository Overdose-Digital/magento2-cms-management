<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Data;

use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ContentVersion extends AbstractExtensibleModel implements ContentVersionInterface
{
    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): ContentVersionInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $identifier): ContentVersionInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): ?string
    {
        return $this->getData(self::VERSION);
    }

    /**
     * @inheritdoc
     */
    public function setVersion(string $version): ContentVersionInterface
    {
        return $this->setData(self::VERSION, $version);
    }

    /**
     * @inheritdoc
     */
    public function getStoreIds(): ?string
    {
        return $this->getData(self::STORE_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setStoreIds(string $storeIds): ContentVersionInterface
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Overdose\CMSContent\Api\Data\ContentVersionExtensionInterface $extensionAttributes
    ): ContentVersionInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
