<?php


namespace Overdose\CMSContent\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface as CmsBlockInterface;
use Magento\Cms\Api\Data\PageInterface as CmsPageInterface;
use Magento\Cms\Model\BlockFactory as CmsBlockFactory;
use Magento\Cms\Model\PageFactory as CmsPageFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as CmsBlockCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Api\ContentImportInterface;

class Import implements ContentImportInterface
{
    const MEDIA_ARCHIVE_PATH = 'media';
    /**
     * @var FileSystem
     */
    private FileSystem $fileSystem;
    /**
     * @var File
     */
    private File $file;

    protected $cmsMode;
    protected $mediaMode;
    protected $storesMap;

    /**
     * @var CmsPageCollectionFactory
     */
    private CmsPageCollectionFactory $pageCollectionFactory;
    /**
     * @var CmsBlockCollectionFactory
     */
    private CmsBlockCollectionFactory $blockCollectionFactory;
    /**
     * @var CmsPageFactory
     */
    private CmsPageFactory $pageFactory;
    /**
     * @var CmsBlockFactory
     */
    private CmsBlockFactory $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $blockRepositoryInterface;
    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepositoryInterface;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializerInterface;

    public function __construct(
        FileSystem $fileSystem,
        File $file,
        CmsPageFactory $pageFactory,
        CmsBlockFactory $blockFactory,

        CmsBlockCollectionFactory $blockCollectionFactory,
        CmsPageCollectionFactory $pageCollectionFactory,

        BlockRepositoryInterface $blockRepositoryInterface,
        StoreRepositoryInterface $storeRepositoryInterface,
        SerializerInterface $serializerInterface
    ) {
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->blockRepositoryInterface = $blockRepositoryInterface;
        $this->storeRepositoryInterface = $storeRepositoryInterface;

        $this->cmsMode = ContentImportInterface::OD_CMS_MODE_UPDATE;
        $this->mediaMode = ContentImportInterface::OD_MEDIA_MODE_UPDATE;

        $this->storesMap = [];
        $stores = $this->storeRepositoryInterface->getList();
        foreach ($stores as $store) {
            $this->storesMap[$store->getCode()] = $store->getCode();
        }
        $this->serializerInterface = $serializerInterface;
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
        $extractPath = $this->fileSystem->getExtractPath($subPath);

        $zipArchive->extractTo($extractPath);
        $zipArchive->close();

        // Check if pages.json exists
        $count = 0;
        foreach (scandir($extractPath. '/') as $path){
            $absolutePath = $extractPath. '/' . $path;
            if(in_array($path, ['.', '..'])) {
                continue;
            }
            if (!$this->file->fileExists($absolutePath, true)) {
                throw new \Exception($path . ' is missing');
            }

            $pathInfo = $this->file->getPathInfo($absolutePath);

            $cmsData = [];
            if($pathInfo['extension'] === 'xml'){
                $xmlfile = file_get_contents($absolutePath);
                $xml = simplexml_load_string($xmlfile);
                $xml = $this->serializerInterface->serialize($xml);
                $cmsData = $this->serializerInterface->unserialize($xml);
            }

            if($pathInfo['extension'] === 'json'){
                $jsonString = $this->file->read($absolutePath);
                $cmsData = $this->serializerInterface->unserialize($jsonString);
            }

            $count += $this->importContentFromArray($cmsData, $extractPath);

            // Remove if necessary
            if ($rm) {
                $this->file->rm($fileName);
            }
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
        if (!isset($payload['pages']) && !isset($payload['blocks'])) {
            throw new \Exception('Invalid json archive');
        }

        $count = 0;

        // Import pages
        if(isset($payload['pages'])){
            foreach ($payload['pages'] as $key => $pageData) {
                if ($this->importPageContentFromArray($pageData)) {
                    $count++;
                }
            }
        }


        if(isset($payload['blocks'])){
            foreach ($payload['blocks'] as $key => $blockData) {
                if ($this->importBlockContentFromArray($blockData)) {
                    $count++;
                }
            }
        }

        // Import media
        if ($archivePath && ($count > 0) && ($this->mediaMode != ContentImportInterface::OD_MEDIA_MODE_NONE)) {
            if (isset($payload['media'])) {
                foreach ($payload['media'] as $mediaFile) {
                    $sourceFile = $archivePath . '/' . self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile;
                    $destFile = $this->fileSystem->getMediaPath($mediaFile);

                    if ($this->file->fileExists($sourceFile, true)) {
                        if ($this->file->fileExists($destFile, true) &&
                            ($this->mediaMode == ContentImportInterface::OD_MEDIA_MODE_SKIP)
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

        $storeIds = $this->getStoreIdsByCodes($this->mapStores($pageData['stores']));

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
            ->setIsActive($cms[CmsPageInterface::IS_ACTIVE]);

        $page->setData('stores', $storeIds);
        $page->save();

        return true;
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

        $storeIds = $this->getStoreIdsByCodes($this->mapStores($blockData['stores']));

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

            if ($this->cmsMode == ContentImportInterface::OD_CMS_MODE_SKIP) {
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
    protected function mapStores($storeCodes): array
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
     * Set CMS mode
     * @param $mode
     * @return ContentImportInterface
     */
    public function setCmsModeOption($mode): ContentImportInterface
    {
        $this->cmsMode = $mode;
        return $this;
    }

    /**
     * Set stores mapping
     * @param array $storesMap
     * @return ContentImportInterface
     */
    public function setStoresMapValue(array $storesMap): ContentImportInterface
    {
        return $this;
    }

    /**
     * Set media mode
     * @param $mode
     * @return ContentImportInterface
     */
    public function setMediaModeOption($mode): ContentImportInterface
    {
        $this->mediaMode = $mode;
        return $this;
    }

}
