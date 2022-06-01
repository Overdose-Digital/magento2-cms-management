<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Converter\Block;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Model\Converter\CmsEntityConverterInterface;

class Converter implements CmsEntityConverterInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepositoryInterface;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepositoryInterface;

    public function __construct(
        StoreRepositoryInterface $storeRepositoryInterface,
        BlockRepositoryInterface $blockRepositoryInterface
    ) {
        $this->storeRepositoryInterface = $storeRepositoryInterface;
        $this->blockRepositoryInterface = $blockRepositoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function convertToArray(array $cmsEntities): array
    {
        $blocks = [];
        $media  = [];

        foreach ($cmsEntities as $cmsEntity) {
            $blockInfo = $this->convertBlockToArray($cmsEntity);
            $blocks[$this->getBlockKey($cmsEntity)] = $blockInfo;
            $media = array_merge($media, $blockInfo['media']);
        }

        return [
            self::BLOCK_ENTITY_CODE => $blocks,
            'media' => $media,
        ];
    }

    /**
     * Return CMS block to array
     * @param BlockInterface $blockInterface
     * @return array
     */
    private function convertBlockToArray(BlockInterface $blockInterface): array
    {
        // Extract attachments
        $media = $this->getMediaAttachments($blockInterface->getContent());

        $payload = [
            'cms' => [
                BlockInterface::IDENTIFIER => $blockInterface->getIdentifier(),
                BlockInterface::TITLE => $blockInterface->getTitle(),
                BlockInterface::CONTENT => $blockInterface->getContent(),
                BlockInterface::IS_ACTIVE => (string)$blockInterface->isActive(),
            ],
            'stores' => $this->getStoreCodes($blockInterface->getStoreId()),
            'media' => $media,
            'block_references' => $this->saveBlockByIdent($blockInterface->getContent()),
        ];

        return $payload;
    }

    /**
     * Get media attachments from content
     * @param $content
     * @return array
     */
    private function getMediaAttachments($content): array
    {
        $result = [];
        if (preg_match_all('/\{\{media.+?url\s*=\s*("|&quot;)(.+?)("|&quot;).*?\}\}/', $content, $matches)) {
            $result += $matches[2];
        }

        if (preg_match_all('/{{media.+?url\s*=\s*(?!"|&quot;)(.+?)}}/', $content, $matches)) {
            $result += $matches[1];
        }

        return $result;
    }

    /**
     * Get block unique key
     *
     * @param BlockInterface $blockInterface
     *
     * @return string
     */
    private function getBlockKey(BlockInterface $blockInterface): string
    {
        $keys = $this->getStoreCodes($blockInterface->getStoreId());
        $keys[] = $blockInterface->getIdentifier();

        return implode(':', $keys);
    }

    /**
     * Get store codes
     * @param array $storeIds
     * @return array
     */
    public function getStoreCodes($storeIds): array
    {
        $return = [];

        foreach ($storeIds as $storeId) {
            $return[] = $this->storeRepositoryInterface->getById($storeId)->getCode();
        }

        return $return;
    }

    /**
     * @param string $content
     *
     * @return array
     * @throws LocalizedException
     */
    private function saveBlockByIdent(string $content)
    {
        $references = [];

        $pattern = '/{{widget.+?block_id\s*=\s*("|&quot;)(\d+?)("|&quot;).*?}}/';

        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[2] as $blockId) {
                $block = $this->blockRepositoryInterface->getById($blockId);
                $references[$blockId] = $block->getIdentifier();
            }
        }

        return $references;
    }
}
