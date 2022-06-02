<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Converter\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Overdose\CMSContent\Model\Converter\AbstractConverter;
use Overdose\CMSContent\Model\Converter\CmsEntityConverterInterface;

class Converter extends AbstractConverter implements CmsEntityConverterInterface
{
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
     * Convert CMS block to array
     *
     * @param BlockInterface $blockInterface
     *
     * @return array
     * @throws LocalizedException
     */
    private function convertBlockToArray(BlockInterface $blockInterface): array
    {
        // Extract attachments
        $media = $this->getMediaAttachments($blockInterface->getContent());

        return [
            'cms' => [
                BlockInterface::IDENTIFIER => $blockInterface->getIdentifier(),
                BlockInterface::TITLE => $blockInterface->getTitle(),
                BlockInterface::CONTENT => $blockInterface->getContent(),
                BlockInterface::IS_ACTIVE => (string)$blockInterface->isActive(),
            ],
            'stores' => $this->getStoreCodes($blockInterface->getStores()),
            'media' => $media,
            'block_references' => $this->saveBlockByIdent($blockInterface->getContent())
        ];
    }

    /**
     * Get block unique key
     *
     * @param BlockInterface $blockInterface
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getBlockKey(BlockInterface $blockInterface): string
    {
        $keys = $this->getStoreCodes($blockInterface->getStores());
        $keys[] = $blockInterface->getIdentifier();

        return implode(':', $keys);
    }
}
