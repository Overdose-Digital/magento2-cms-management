<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Model\AbstractModel;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion as ResourceContentVersion;

class ContentVersion extends AbstractModel implements ContentVersionInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceContentVersion::class);
    }

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
