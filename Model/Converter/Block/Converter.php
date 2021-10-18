<?php


namespace Overdose\CMSContent\Model\Converter\Block;


use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface as CmsBlockInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Api\CmsEntityConverterInterface;

class Converter implements CmsEntityConverterInterface
{
    const CMS_ENTITY_TYPE = \Magento\Cms\Api\Data\BlockInterface::class;

    const CMS_ENTITY_CODE = 'blocks';

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
     * @return string
     */
    public function getCmsEntityType(): string
    {
        return self::CMS_ENTITY_TYPE;
    }

    /**
     * @return string
     */
    public function getCmsEntityCode(): string
    {
        return self::CMS_ENTITY_CODE;
    }

    /**
     * @param array $cmsEntities
     * @return array
     */
    public function convertToArray(array $cmsEntities): array
    {
        $blocks = [];
        $media = [];

        foreach ($cmsEntities as $cmsEntity) {
            $blockInfo = $this->convertBlockToArray($cmsEntity);
            $blocks[$this->_getBlockKey($cmsEntity)] = $blockInfo;
            $media = array_merge($media, $blockInfo['media']);
        }

        return [
            'blocks' => $blocks,
            'media' => $media,
        ];
    }

    /**
     * Return CMS block to array
     * @param \Magento\Cms\Api\Data\BlockInterface $blockInterface
     * @return array
     */
    private function convertBlockToArray(CmsBlockInterface $blockInterface): array
    {
        // Extract attachments
        $media = $this->getMediaAttachments($blockInterface->getContent());

        $payload = [
            'cms' => [
                CmsBlockInterface::IDENTIFIER => $blockInterface->getIdentifier(),
                CmsBlockInterface::TITLE => $blockInterface->getTitle(),
                CmsBlockInterface::CONTENT => $blockInterface->getContent(),
                CmsBlockInterface::IS_ACTIVE => (string)$blockInterface->isActive(),
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
     * @param CmsBlockInterface $blockInterface
     * @return string
     */
    private function _getBlockKey(CmsBlockInterface $blockInterface): string
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveBlockByIdent(string $content)
    {
        $references = [];

        if (preg_match_all('/{{widget.+?block_id\s*=\s*("|&quot;)(\d+?)("|&quot;).*?}}/', $content, $matches)) {
            foreach ($matches[2] as $blockId) {
                $block = $this->blockRepositoryInterface->getById($blockId);
                $references[$blockId] = $block->getIdentifier();
            }
        }

        return $references;
    }
}
