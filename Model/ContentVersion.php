<?php

namespace Overdose\CMSContent\Model;

use Magento\Framework\Model\AbstractModel;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;

class ContentVersion extends AbstractModel implements ContentVersionInterface
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
}
