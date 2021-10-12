<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface as CmsPageInterface;
use Magento\Cms\Api\Data\BlockInterface as CmsBlockInterface;
use Magento\Cms\Model\BlockFactory as CmsBlockFactory;
use Magento\Cms\Model\PageFactory as CmsPageFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as CmsBlockCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;
use Overdose\CMSContent\Api\ContentImportExportInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Exception\NoSuchEntityException;

class ImportExport implements ContentImportExportInterface
{
    const FILENAME = 'cms';
    const MEDIA_ARCHIVE_PATH = 'media';

    protected $storeRepositoryInterface;
    protected $pageCollectionFactory;
    protected $blockCollectionFactory;
    protected $blockRepositoryInterface;
    protected $pageFactory;
    protected $blockFactory;
    protected $filesystem;
    protected $file;
    protected $dateTime;

    protected $cmsMode;
    protected $mediaMode;
    protected $storesMap;
    /**
     * @var SerializerInterface
     */
    protected $serializerInterface;
    /**
     * @var CmsEntityConverterManagerInterface
     */
    private $cmsEntityConverterManager;
    /**
     * @var CmsEntityGeneratorManagerInterface
     */
    private $cmsEntityGeneratorManager;

    /**
     * @param StoreRepositoryInterface $storeRepositoryInterface
     * @param SerializerInterface $serializerInterface
     * @param CmsPageFactory $pageFactory
     * @param CmsPageCollectionFactory $pageCollectionFactory
     * @param CmsBlockFactory $blockFactory
     * @param CmsBlockCollectionFactory $blockCollectionFactory
     * @param BlockRepositoryInterface $blockRepositoryInterface
     * @param Filesystem $filesystem
     * @param File $file
     * @param DateTime $dateTime
     */
    public function __construct(
        StoreRepositoryInterface $storeRepositoryInterface,
        SerializerInterface $serializerInterface,
        CmsPageFactory $pageFactory,
        CmsPageCollectionFactory $pageCollectionFactory,
        CmsBlockFactory $blockFactory,
        CmsBlockCollectionFactory $blockCollectionFactory,
        BlockRepositoryInterface $blockRepositoryInterface,
        Filesystem $filesystem,
        File $file,
        DateTime $dateTime,

        CmsEntityConverterManagerInterface $cmsEntityConverterManager,
        CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager
    ) {
        $this->storeRepositoryInterface = $storeRepositoryInterface;
        $this->serializerInterface = $serializerInterface;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->pageFactory = $pageFactory;
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockFactory = $blockFactory;
        $this->blockRepositoryInterface = $blockRepositoryInterface;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->dateTime = $dateTime;

        $this->cmsMode = ContentImportExportInterface::OD_CMS_MODE_UPDATE;
        $this->mediaMode = ContentImportExportInterface::OD_MEDIA_MODE_UPDATE;

        $this->storesMap = [];
        $stores = $this->storeRepositoryInterface->getList();
        foreach ($stores as $store) {
            $this->storesMap[$store->getCode()] = $store->getCode();
        }
        $this->cmsEntityConverterManager = $cmsEntityConverterManager;
        $this->cmsEntityGeneratorManager = $cmsEntityGeneratorManager;
    }

    /**
     * Create a zip file and return its name
     * @param \Magento\Cms\Api\Data\PageInterface[] | \Magento\Cms\Api\Data\BlockInterface[] $cmsEntities
     * @param string $type
     * @param string $fileName
     * @return string
     */
    public function createZipFile(array $cmsEntities, string $type, string $fileName = null): string
    {
        $exportPath = $this->filesystem->getExportPath();

        $zipFile = $exportPath . '/' . $fileName;
        $relativeZipFile = Filesystem::EXPORT_PATH . '/' . $fileName;

        $zipArchive = new \ZipArchive();
        $zipArchive->open($zipFile, \ZipArchive::CREATE);

        $converter = $this->cmsEntityConverterManager
            ->setEntities($cmsEntities)
            ->getConverter();
        $contentArray = $converter->convertToArray($cmsEntities);

        $cmsEntityCode = $converter->getCmsEntityCode();
        foreach ($contentArray[$cmsEntityCode] as $key => $content) {
            $payload = $this->cmsEntityGeneratorManager
                ->getGenerator($type)
                ->generate([
                    $cmsEntityCode => [$key => $content],
                    'media' => $contentArray['media']
                ]);
            $zipArchive->addFromString(sprintf('%s-%s-%s.%s', self::FILENAME, $cmsEntityCode, $key, $type), $payload);
        }

        // Add media files
        foreach ($contentArray['media'] as $mediaFile) {
            //Strip Quotes if any
            $mediaFile = str_replace(['"',"&quot;","'"], '', $mediaFile);
            $absMediaPath = $this->filesystem->getMediaPath($mediaFile);
            if ($this->file->fileExists($absMediaPath, true)) {
                $zipArchive->addFile($absMediaPath, self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile);
            }
        }

        $zipArchive->close();

        // Clear export path
        $this->file->rm($exportPath, true);

        return $relativeZipFile;
    }

    /**
     * Return CMS pages as array
     * @param \Magento\Cms\Api\Data\PageInterface[] $pageInterfaces
     * @return array
     */
    public function convertPagesToArray(array $pageInterfaces): array
    {
        $pages = [];
        $media = [];

        foreach ($pageInterfaces as $pageInterface) {
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
                CmsPageInterface::IS_ACTIVE => $pageInterface->isActive(),
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
    public function getMediaAttachments($content): array
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
     * Get page unique key
     * @param CmsPageInterface $pageInterface
     * @return string
     */
    protected function _getPageKey(CmsPageInterface $pageInterface): string
    {
        $keys = $this->getStoreCodes($pageInterface->getStoreId());
        $keys[] = $pageInterface->getIdentifier();

        return implode(':', $keys);
    }

    /**
     * Return CMS blocks as array
     * @param \Magento\Cms\Api\Data\BlockInterface[] $blockInterfaces
     * @return array
     */
    public function convertBlocksToArray(array $blockInterfaces): array
    {
        $blocks = [];
        $media = [];

        foreach ($blockInterfaces as $blockInterface) {
            $blockInfo = $this->convertBlockToArray($blockInterface);
            $blocks[$this->_getBlockKey($blockInterface)] = $blockInfo;
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
    public function convertBlockToArray(CmsBlockInterface $blockInterface): array
    {
        // Extract attachments
        $media = $this->getMediaAttachments($blockInterface->getContent());

        $payload = [
            'cms' => [
                CmsBlockInterface::IDENTIFIER => $blockInterface->getIdentifier(),
                CmsBlockInterface::TITLE => $blockInterface->getTitle(),
                CmsBlockInterface::CONTENT => $blockInterface->getContent(),
                CmsBlockInterface::IS_ACTIVE => $blockInterface->isActive(),
            ],
            'stores' => $this->getStoreCodes($blockInterface->getStoreId()),
            'media' => $media,
            'block_references' => $this->saveBlockByIdent($blockInterface->getContent()),
        ];

        return $payload;
    }

    /**
     * Get block unique key
     * @param CmsBlockInterface $blockInterface
     * @return string
     */
    protected function _getBlockKey(CmsBlockInterface $blockInterface): string
    {
        $keys = $this->getStoreCodes($blockInterface->getStoreId());
        $keys[] = $blockInterface->getIdentifier();

        return implode(':', $keys);
    }

    /**
     * Import contents from zip archive and return number of imported records (-1 on error)
     * @param string $fileName
     * @param bool $rm = true
     * @return int
     * @throws \Exception
     */
    public function importContentFromZipFile($fileName, $rm = false): int
    {
        // Unzip archive
        $zipArchive = new \ZipArchive();
        $res = $zipArchive->open($fileName);
        if ($res !== true) {
            throw new \Exception('Cannot open ZIP archive');
        }

        $subPath = md5(date(DATE_RFC2822));
        $extractPath = $this->filesystem->getExtractPath($subPath);

        $zipArchive->extractTo($extractPath);
        $zipArchive->close();

        // Check if pages.json exists
        $pagesFile = $extractPath . '/' . self::JSON_FILENAME;
        if (!$this->file->fileExists($pagesFile, true)) {
            throw new \Exception(self::JSON_FILENAME . ' is missing');
        }

        // Read and import
        $jsonString = $this->file->read($pagesFile);
        $cmsData = $this->serializerInterface->unserialize($jsonString);

        $count = $this->importContentFromArray($cmsData, $extractPath);

        // Remove if necessary
        if ($rm) {
            $this->file->rm($fileName);
        }

        // Clear archive
        $this->file->rmdir($extractPath, true);

        return $count;
    }

    /**
     * Import contents from array and return number of imported records (-1 on error)
     * @param array $payload
     * @param string $archivePath = null
     * @return int
     * @throws \Exception
     */
    public function importContentFromArray(array $payload, $archivePath = null): int
    {
        if (!isset($payload['pages']) && !isset($payload['b locks'])) {
            throw new \Exception('Invalid json archive');
        }

        $count = 0;

        // Import pages
        foreach ($payload['pages'] as $key => $pageData) {
            if ($this->importPageContentFromArray($pageData)) {
                $count++;
            }
        }

        // Import blocks
        foreach ($payload['blocks'] as $key => $blockData) {
            if ($this->importBlockContentFromArray($blockData)) {
                $count++;
            }
        }

        // Import media
        if ($archivePath && ($count > 0) && ($this->mediaMode != ContentImportExportInterface::OD_MEDIA_MODE_NONE)) {
            foreach ($payload['media'] as $mediaFile) {
                $sourceFile = $archivePath . '/' . self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile;
                $destFile = $this->filesystem->getMediaPath($mediaFile);

                if ($this->file->fileExists($sourceFile, true)) {
                    if ($this->file->fileExists($destFile, true) &&
                        ($this->mediaMode == ContentImportExportInterface::OD_MEDIA_MODE_SKIP)
                    ) {
                        continue;
                    }

                    if (!$this->file->fileExists(dirname($destFile), false)) {
                        if (!$this->file->mkdir(dirname($destFile))) {
                            throw new \Exception('Unable to create folder: ' . dirname($destFile));
                        }
                    }
                    if (!$this->file->cp($sourceFile, $destFile)) {
                        throw new \Exception('Unable to save image: ' . $mediaFile);
                    }
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Import a single page from an array and return false on error and true on success
     * @param array $pageData
     * @return bool
     * @throws \Exception
     */
    public function importPageContentFromArray(array $pageData): bool
    {
        // Process block identifiers
        $pageData = $this->loadBlocksByIdent($pageData, CmsPageInterface::IDENTIFIER);

        // Will not use repositories to save pages because it does not allow stores selection

        $storeIds = $this->getStoreIdsByCodes($this->_mapStores($pageData['stores']));

        $collection = $this->pageCollectionFactory->create();
        $collection
            ->addFieldToFilter(CmsPageInterface::IDENTIFIER, $pageData['cms'][CmsPageInterface::IDENTIFIER]);

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

        $page = $this->pageFactory->create();
        if ($pageId) {
            $page->load($pageId);

            if ($this->cmsMode == ContentImportExportInterface::OD_CMS_MODE_SKIP) {
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
            ->setIsActive($cms[CmsPageInterface::IS_ACTIVE]);

        $page->setData('stores', $storeIds);
        $page->save();

        return true;
    }

    /**
     * Get store ids by codes
     * @param array $storeCodes
     * @return array
     */
    public function getStoreIdsByCodes(array $storeCodes): array
    {
        $return = [];
        foreach ($storeCodes as $storeCode) {
            if ($storeCode == 'admin') {
                $return[] = 0;
            } else {
                $store = $this->storeRepositoryInterface->get($storeCode);
                if ($store && $store->getId()) {
                    $return[] = $store->getId();
                }
            }
        }

        return $return;
    }

    /**
     * Map stores
     * @param $storeCodes
     * @return array
     */
    protected function _mapStores($storeCodes): array
    {
        $return = [];
        foreach ($storeCodes as $storeCode) {
            foreach ($this->storesMap as $to => $from) {
                if ($storeCode == $from) {
                    $return[] = $to;
                }
            }
        }

        return $return;
    }

    /**
     * Import a single block from an array and return false on error and true on success
     * @param array $blockData
     * @return bool
     * @throws \Exception
     */
    public function importBlockContentFromArray(array $blockData): bool
    {
        // Process block identifiers
        $blockData = $this->loadBlocksByIdent($blockData, CmsBlockInterface::IDENTIFIER);

        // Will not use repositories to save blocks because it does not allow stores selection

        $storeIds = $this->getStoreIdsByCodes($this->_mapStores($blockData['stores']));

        $collection = $this->blockCollectionFactory->create();
        $collection
            ->addFieldToFilter(CmsBlockInterface::IDENTIFIER, $blockData['cms'][CmsBlockInterface::IDENTIFIER]);

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

        $block = $this->blockFactory->create();
        if ($blockId) {
            $block->load($blockId);

            if ($this->cmsMode == ContentImportExportInterface::OD_CMS_MODE_SKIP) {
                return false;
            }
        }

        $cms = $blockData['cms'];

        $block
            ->setIdentifier($cms[CmsBlockInterface::IDENTIFIER])
            ->setTitle($cms[CmsBlockInterface::TITLE])
            ->setContent($cms[CmsBlockInterface::CONTENT])
            ->setIsActive($cms[CmsBlockInterface::IS_ACTIVE]);

        $block->setData('stores', $storeIds);
        $block->save();

        return true;
    }

    /**
     * Set CMS mode
     * @param $mode
     * @return ContentImportExportInterface
     */
    public function setCmsModeOption($mode): ContentImportExportInterface
    {
        $this->cmsMode = $mode;
        return $this;
    }

    /**
     * Set media mode
     * @param $mode
     * @return ContentImportExportInterface
     */
    public function setMediaModeOption($mode): ContentImportExportInterface
    {
        $this->mediaMode = $mode;
        return $this;
    }

    /**
     * Set stores mapping
     * @param array $storesMap
     * @return ContentImportExportInterface
     */
    public function setStoresMapValue(array $storesMap): ContentImportExportInterface
    {
        return $this;
    }

    /**
     * @param string $content
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveBlockByIdent(string $content)
    {
        $references = [];

        if (preg_match_all('/{{widget.+?block_id\s*=\s*("|&quot;)(\d+?)("|&quot;).*?}}/', $content, $matches)) {
            foreach ($matches[2] as $blockId) {
                $block                = $this->blockRepositoryInterface->getById($blockId);
                $references[$blockId] = $block->getIdentifier();
            }
        }

        return $references;
    }

    /**
     * @param array  $cmsData
     * @param string $contentKey
     *
     * @return array[]
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadBlocksByIdent(array $cmsData, string $contentKey)
    {
        if (isset($cmsData['block_references'])) {
            $pairs = [];
            foreach ($cmsData['block_references'] as $blockId => $blockIdent) {
                $block           = $this->blockRepositoryInterface->getById($blockIdent);
                $pairs[$blockId] = $block->getId();
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
}
