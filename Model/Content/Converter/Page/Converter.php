<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Content\Converter\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Overdose\CMSContent\Model\Content\Converter\AbstractConverter;
use Overdose\CMSContent\Model\Content\Converter\CmsEntityConverterInterface;

class Converter extends AbstractConverter implements CmsEntityConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convertToArray(array $cmsEntities): array
    {
        $pages = [];
        $media = [];

        foreach ($cmsEntities as $pageInterface) {
            $pageInfo = $this->convertPageToArray($pageInterface);
            $pages[$this->getPageKey($pageInterface)] = $pageInfo;
            $media = array_merge($media, $pageInfo['media']);
        }

        return [
            self::PAGE_ENTITY_CODE => $pages,
            'media' => $media,
        ];
    }

    /**
     * Return CMS page to array
     *
     * @param PageInterface $pageInterface
     *
     * @return array
     * @throws LocalizedException
     */
    public function convertPageToArray(PageInterface $pageInterface): array
    {
        // Extract attachments
        $media = $this->getMediaAttachments($pageInterface->getContent());

         return [
            'cms' => [
                PageInterface::IDENTIFIER => $pageInterface->getIdentifier(),
                PageInterface::TITLE => $pageInterface->getTitle(),
                PageInterface::PAGE_LAYOUT => $pageInterface->getPageLayout(),
                PageInterface::META_KEYWORDS => $pageInterface->getMetaKeywords(),
                PageInterface::META_DESCRIPTION => $pageInterface->getMetaDescription(),
                PageInterface::CONTENT_HEADING => $pageInterface->getContentHeading(),
                PageInterface::CONTENT => $pageInterface->getContent(),
                PageInterface::SORT_ORDER => $pageInterface->getSortOrder(),
                PageInterface::LAYOUT_UPDATE_XML => $pageInterface->getLayoutUpdateXml(),
                PageInterface::CUSTOM_THEME => $pageInterface->getCustomTheme(),
                PageInterface::CUSTOM_ROOT_TEMPLATE => $pageInterface->getCustomRootTemplate(),
                PageInterface::CUSTOM_LAYOUT_UPDATE_XML => $pageInterface->getCustomLayoutUpdateXml(),
                PageInterface::CUSTOM_THEME_FROM => $pageInterface->getCustomThemeFrom(),
                PageInterface::CUSTOM_THEME_TO => $pageInterface->getCustomThemeTo(),
                PageInterface::IS_ACTIVE => (string)$pageInterface->isActive(),
            ],
            'stores' => $this->getStoreCodes($pageInterface->getStores()),
            'media' => $media,
            'block_references' => $this->saveBlockByIdent($pageInterface->getContent()),
        ];
    }

    /**
     * Get page unique key
     *
     * @param PageInterface $pageInterface
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPageKey(PageInterface $pageInterface): string
    {
        $keys = $this->getStoreCodes($pageInterface->getStores());
        $keys[] = $pageInterface->getIdentifier();

        return implode(':', $keys);
    }
}
