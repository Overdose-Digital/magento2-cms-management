<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Content;

use Exception;
use Magento\Cms\Api\Data\BlockInterface as CmsBlockInterface;
use Magento\Cms\Api\Data\PageInterface as CmsPageInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\SerializerInterface;
use Overdose\CMSContent\Api\ContentImportInterface;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;
use Overdose\CMSContent\Api\StoreManagementInterface;
use Overdose\CMSContent\File\FileManagerInterface;
use Overdose\CMSContent\Model\Config;
use Overdose\CMSContent\Model\EntityManagement;

class Import implements ContentImportInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FileManagerInterface
     */
    private $fileManager;

    /**
     * @var File
     */
    private $file;

    /**
     * @var string
     */
    private $cmsMode = ContentImportInterface::OD_CMS_MODE_UPDATE;

    /**
     * @var string
     */
    private $mediaMode = ContentImportInterface::OD_MEDIA_MODE_UPDATE;

    /**
     * @var StoreManagementInterface
     */
    private $storeManagement;

    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var ContentVersionManagementInterface
     */
    private $contentVersionManagement;

    /**
     * @var EntityManagement
     */
    private $entityManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param FileManagerInterface $fileManager
     * @param Config $config
     * @param File $file
     * @param StoreManagementInterface $storeManagement
     * @param ContentVersionManagementInterface $contentVersionManagement
     * @param SerializerInterface $serializerInterface
     * @param EntityManagement $entityManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        FileManagerInterface $fileManager,
        Config $config,
        File $file,
        StoreManagementInterface $storeManagement,
        ContentVersionManagementInterface $contentVersionManagement,
        SerializerInterface $serializerInterface,
        EntityManagement $entityManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->fileManager = $fileManager;
        $this->config = $config;
        $this->file = $file;
        $this->storeManagement = $storeManagement;
        $this->serializerInterface = $serializerInterface;
        $this->contentVersionManagement = $contentVersionManagement;
        $this->entityManagement = $entityManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function importContentFromZipFile(string $fileName, bool $rm): int
    {
        $zipArchive = new \ZipArchive();
        $res = $zipArchive->open($fileName);
        if ($res !== true) {
            throw new Exception('Cannot open ZIP archive');
        }

        $subPath = md5(date(DATE_RFC2822));
        $extractPath = $this->fileManager->getFolder(
            $this->config->getExtractPath() . DIRECTORY_SEPARATOR . $subPath
        );

        $zipArchive->extractTo($extractPath);
        $zipArchive->close();

        $count = 0;
        foreach (scandir($extractPath. '/') as $path) {
            $absolutePath = $extractPath. '/' . $path;
            if (in_array($path, ['.', '..']) || is_dir($path)) {
                continue;
            }
            if (!$this->file->fileExists($absolutePath, true)) {
                throw new Exception($path . ' is missing');
            }

            $pathInfo = $this->file->getPathInfo($absolutePath);

            if (!isset($pathInfo['extension'])) {
                continue;
            }
            switch ($pathInfo['extension']) {
                case 'xml':
                    $count += $this->contentVersionManagement->processFile($absolutePath);
                    break;

                case 'json':
                    $cmsData = $this->serializerInterface->unserialize(
                        $this->file->read($absolutePath)
                    );
                    $count += $this->importContentFromArray($cmsData, $extractPath);
                    break;
            }

            // Remove if necessary
            if ($rm) {
                $this->file->rm($fileName);
            }
        }

        // Clear extracted folder
        $this->file->rmdir($extractPath, true);

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function setCmsModeOption($mode): ContentImportInterface
    {
        $this->cmsMode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setMediaModeOption($mode): ContentImportInterface
    {
        $this->mediaMode = $mode;

        return $this;
    }

    /**
     * Import contents from array and return number of imported records (-1 on error)
     *
     * @param array $payload
     * @param string|null $archivePath = null
     *
     * @return int
     * @throws Exception
     */
    public function importContentFromArray(array $payload, ?string $archivePath = null): int
    {
        if (isset($payload['config'])) {
            $payload = $payload['config'];
        }
        if (!isset($payload['pages']) && !isset($payload['blocks'])) {
            throw new Exception('Invalid import archive');
        }

        $count = 0;

        // Import pages
        if (isset($payload['pages'])) {
            foreach ($payload['pages'] as $key => $pageData) {
                if ($this->importPageContentFromArray($pageData)) {
                    $count++;
                }
            }
        }

        if (isset($payload['blocks'])) {
            foreach ($payload['blocks'] as $key => $blockData) {
                if ($this->importBlockContentFromArray($blockData)) {
                    $count++;
                }
            }
        }

        return $this->importMedia($payload, $archivePath, $count);
    }

    /**
     * Import a single page from an array and return false on error and true on success
     * @param array $pageData
     * @return bool
     * @throws Exception
     */
    public function importPageContentFromArray(array $pageData): bool
    {
        $pageData = $this->loadBlocksByIdent($pageData, CmsPageInterface::IDENTIFIER);

        $storeIds = $this->storeManagement->getStoreIdsByCodes($pageData['stores']);

        $collection = $this->entityManagement
            ->getRepository(EntityManagement::TYPE_PAGE)
            ->getList(
                $this->searchCriteriaBuilder->addFilter(
                    CmsBlockInterface::IDENTIFIER,
                    $pageData['cms'][CmsBlockInterface::IDENTIFIER]
                )->create()
            )->getItems();

        $pageId = 0;
        foreach ($collection as $item) {
            $storesIntersect = array_intersect($item->getStoreId(), $storeIds);

            // @codingStandardsIgnoreStart
            if (count($storesIntersect)) {
                // @codingStandardsIgnoreEnd
                $pageId = $item->getId();
                break;
            }
        }

        $page = $this->entityManagement->getFactory(EntityManagement::TYPE_PAGE);
        if ($pageId) {
            $page->load($pageId);

            if ($this->cmsMode == ContentImportInterface::OD_CMS_MODE_SKIP) {
                return false;
            }
        }

        $cms = $pageData['cms'];

        $page
            ->setIdentifier($cms[CmsPageInterface::IDENTIFIER])
            ->setTitle($cms[CmsPageInterface::TITLE])
            ->setPageLayout($cms[CmsPageInterface::PAGE_LAYOUT])
            ->setMetaKeywords($cms[CmsPageInterface::META_KEYWORDS])
            ->setMetaDescription($cms[CmsPageInterface::META_DESCRIPTION])
            ->setContentHeading($cms[CmsPageInterface::CONTENT_HEADING])
            ->setContent($cms[CmsPageInterface::CONTENT])
            ->setSortOrder($cms[CmsPageInterface::SORT_ORDER])
            ->setLayoutUpdateXml($cms[CmsPageInterface::LAYOUT_UPDATE_XML])
            ->setCustomTheme($cms[CmsPageInterface::CUSTOM_THEME])
            ->setCustomRootTemplate($cms[CmsPageInterface::CUSTOM_ROOT_TEMPLATE])
            ->setCustomLayoutUpdateXml($cms[CmsPageInterface::CUSTOM_LAYOUT_UPDATE_XML])
            ->setCustomThemeFrom($cms[CmsPageInterface::CUSTOM_THEME_FROM])
            ->setCustomThemeTo($cms[CmsPageInterface::CUSTOM_THEME_TO])
            ->setIsActive($cms[CmsPageInterface::IS_ACTIVE])
            ->setData('store_id', $storeIds);

        $this->entityManagement->getRepository(EntityManagement::TYPE_PAGE)->save($page);

        return true;
    }

    /**
     * Import a single block from an array and return false on error and true on success
     * @param array $blockData
     * @return bool
     * @throws Exception
     */
    public function importBlockContentFromArray(array $blockData): bool
    {
        // Process block identifiers
        $blockData = $this->loadBlocksByIdent($blockData, CmsBlockInterface::IDENTIFIER);
        $storeIds = $this->storeManagement->getStoreIdsByCodes($blockData['stores']);

        $collection = $this->entityManagement
            ->getRepository(EntityManagement::TYPE_BLOCK)
            ->getList(
                $this->searchCriteriaBuilder->addFilter(
                    CmsBlockInterface::IDENTIFIER,
                    $blockData['cms'][CmsBlockInterface::IDENTIFIER]
                )->create()
            )->getItems();

        $blockId = 0;
        foreach ($collection as $item) {
            $storesIntersect = array_intersect($item->getStoreId(), $storeIds);

            // @codingStandardsIgnoreStart
            if (count($storesIntersect)) {
                // @codingStandardsIgnoreEnd
                $blockId = $item->getId();
                break;
            }
        }

        $block = $this->entityManagement->getFactory(EntityManagement::TYPE_BLOCK);
        if ($blockId) {
            $block->load($blockId);

            if ($this->cmsMode == ContentImportInterface::OD_CMS_MODE_SKIP) {
                return false;
            }
        }

        $cms = $blockData['cms'];

        $block
            ->setIdentifier($cms[CmsBlockInterface::IDENTIFIER])
            ->setTitle($cms[CmsBlockInterface::TITLE])
            ->setContent($cms[CmsBlockInterface::CONTENT])
            ->setIsActive($cms[CmsBlockInterface::IS_ACTIVE])
            ->setData('store_id', $storeIds);

        $this->entityManagement->getRepository(EntityManagement::TYPE_BLOCK)->save($block);

        return true;
    }

    /**
     * @param array  $cmsData
     * @param string $contentKey
     * @return array[]
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadBlocksByIdent(array $cmsData, string $contentKey)
    {
        if (isset($cmsData['block_references'])) {
            $pairs = [];

            $blockRepository = $this->entityManagement->getRepository(EntityManagement::TYPE_BLOCK);

            foreach ($cmsData['block_references'] as $blockId => $blockIdent) {
                if (is_array($blockIdent)) {
                    foreach ($blockIdent as $blockIdentItem) {
                        $block = $blockRepository->getById($blockIdentItem);
                        $pairs[$blockId] = $block->getId();
                    }
                } else {
                    $block = $blockRepository->getById($blockIdent);
                    $pairs[$blockId] = $block->getId();
                }
            }

            $cmsData['cms'][$contentKey] = preg_replace_callback(
                '/({{widget.+?block_id\s*=\s*)("|&quot;)(\d+?)("|&quot;)(.*?}})/',
                function ($matches) use ($pairs) {
                    if (isset($pairs[$matches[3]])) {
                        return $matches[1] . $matches[2] . $pairs[$matches[3]] . $matches[4] . $matches[5];
                    }

                    return $matches[0];
                },
                $cmsData['cms'][$contentKey]
            );
        }

        return $cmsData;
    }

    /**
     * Import media data
     *
     * @param array $payload
     * @param string $archivePath
     * @param int $count
     *
     * @return int
     * @throws FileSystemException
     * @throws Exception
     */
    private function importMedia(array $payload, string $archivePath, int $count): int
    {
        // Import media
        if ($archivePath && ($count > 0) && ($this->mediaMode != ContentImportInterface::OD_MEDIA_MODE_NONE)) {
            if (isset($payload['media'])) {
                foreach ($payload['media'] as $mediaFile) {
                    $sourceFile = $archivePath . '/' . self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile;
                    $destFile = $this->fileManager->getMediaPath($mediaFile);

                    if ($this->file->fileExists($sourceFile, true)) {
                        if ($this->file->fileExists($destFile, true) &&
                            ($this->mediaMode == ContentImportInterface::OD_MEDIA_MODE_SKIP)
                        ) {
                            continue;
                        }

                        if (!$this->file->fileExists(dirname($destFile), false)) {
                            if (!$this->file->mkdir(dirname($destFile))) {
                                throw new Exception('Unable to create folder: ' . dirname($destFile));
                            }
                        }

                        if (!$this->file->cp($sourceFile, $destFile)) {
                            throw new Exception('Unable to save image: ' . $mediaFile);
                        }
                        $count++;
                    }
                }
            }
        }
        return $count;
    }
}
