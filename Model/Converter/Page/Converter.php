<?php

namespace Overdose\CMSContent\Model\Converter\Page;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface as CmsPageInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Api\CmsEntityConverterInterface;

class Converter implements CmsEntityConverterInterface
{
    const CMS_ENTITY_TYPE = \Magento\Cms\Api\Data\PageInterface::class;

    const CMS_ENTITY_CODE = 'pages';

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
        $pages = [];
        $media = [];

        foreach ($cmsEntities as $pageInterface) {
            $pageInfo = $this->convertPageToArray($pageInterface);
            $pages[$this->_getPageKey($pageInterface)] = $pageInfo;
            $media = array_merge($media, $pageInfo['media']);
        }

        return [
            'pages' => $pages,
            'media' => $media,
        ];
    }

    /**
     * Return CMS page to array
     * @param \Magento\Cms\Api\Data\PageInterface $pageInterface
     * @return array
     */
    public function convertPageToArray(CmsPageInterface $pageInterface): array
    {
        // Extract attachments
        $media = $this->getMediaAttachments($pageInterface->getContent());

        $payload = [
            'cms' => [
                CmsPageInterface::IDENTIFIER => $pageInterface->getIdentifier(),
                CmsPageInterface::TITLE => $pageInterface->getTitle(),
                CmsPageInterface::PAGE_LAYOUT => $pageInterface->getPageLayout(),
                CmsPageInterface::META_KEYWORDS => $pageInterface->getMetaKeywords(),
                CmsPageInterface::META_DESCRIPTION => $pageInterface->getMetaDescription(),
                CmsPageInterface::CONTENT_HEADING => $pageInterface->getContentHeading(),
                CmsPageInterface::CONTENT => $pageInterface->getContent(),
                CmsPageInterface::SORT_ORDER => $pageInterface->getSortOrder(),
                CmsPageInterface::LAYOUT_UPDATE_XML => $pageInterface->getLayoutUpdateXml(),
                CmsPageInterface::CUSTOM_THEME => $pageInterface->getCustomTheme(),
                CmsPageInterface::CUSTOM_ROOT_TEMPLATE => $pageInterface->getCustomRootTemplate(),
                CmsPageInterface::CUSTOM_LAYOUT_UPDATE_XML => $pageInterface->getCustomLayoutUpdateXml(),
                CmsPageInterface::CUSTOM_THEME_FROM => $pageInterface->getCustomThemeFrom(),
                CmsPageInterface::CUSTOM_THEME_TO => $pageInterface->getCustomThemeTo(),
                CmsPageInterface::IS_ACTIVE => (string)$pageInterface->isActive(),
            ],
            'stores' => $this->getStoreCodes($pageInterface->getStoreId()),
            'media' => $media,
            'block_references' => $this->saveBlockByIdent($pageInterface->getContent()),
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
     * Get page unique key
     * @param CmsPageInterface $pageInterface
     * @return string
     */
    private function _getPageKey(CmsPageInterface $pageInterface): string
    {
        $keys = $this->getStoreCodes($pageInterface->getStoreId());
        $keys[] = $pageInterface->getIdentifier();

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
