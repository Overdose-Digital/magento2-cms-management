<?php

namespace Overdose\CMSContent\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;
use Overdose\CMSContent\Api\ContentVersionRepositoryInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterfaceFactory;
use Overdose\CMSContent\Exception\InvalidXmlImportFilesException;
use Overdose\CMSContent\Model\Config\Block\Reader as BlocksConfigReader;
use Overdose\CMSContent\Model\Config\Page\Reader as PagesConfigReader;
use Overdose\CMSContent\Model\Config\ReaderAbstract;
use Overdose\CMSContent\Model\Content\Generator\CmsEntityGeneratorInterface;
use Psr\Log\LoggerInterface;

class ContentVersionManagement implements ContentVersionManagementInterface
{
    /**
     * @var array
     */
    private $currentImportItem = [];

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var ContentVersionInterfaceFactory
     */
    private $contentVersionFactory;
    /**
     * @var ContentVersionRepositoryInterface
     */
    private $contentVersionRepository;
    /**
     * @var BlocksConfigReader
     */
    private $blockConfigReader;
    /**
     * @var PagesConfigReader
     */
    private $pagesConfigReader;
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;
    /**
     * @var BlockInterfaceFactory
     */
    private $blockInterfaceFactory;
    /**
     * @var BackupManager
     */
    private $backupManager;
    /**
     * @var PageInterfaceFactory
     */
    private $pageInterfaceFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ContentVersionManagement constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param BlockRepositoryInterface $blockRepository
     * @param PageRepositoryInterface $pageRepository
     * @param BlockInterfaceFactory $blockInterfaceFactory
     * @param PageInterfaceFactory $pageInterfaceFactory
     * @param ContentVersionInterfaceFactory $contentVersionFactory
     * @param ContentVersionRepositoryInterface $contentVersionRepository
     * @param BlocksConfigReader $blockConfigReader
     * @param PagesConfigReader $pagesConfigReader
     * @param BackupManager $backupManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        BlockRepositoryInterface $blockRepository,
        PageRepositoryInterface $pageRepository,
        BlockInterfaceFactory $blockInterfaceFactory,
        PageInterfaceFactory $pageInterfaceFactory,
        ContentVersionInterfaceFactory $contentVersionFactory,
        ContentVersionRepositoryInterface $contentVersionRepository,
        BlocksConfigReader $blockConfigReader,
        PagesConfigReader $pagesConfigReader,
        BackupManager $backupManager,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->blockRepository = $blockRepository;
        $this->pageRepository = $pageRepository;
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionRepository = $contentVersionRepository;
        $this->blockConfigReader = $blockConfigReader;
        $this->pagesConfigReader = $pagesConfigReader;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->blockInterfaceFactory = $blockInterfaceFactory;
        $this->backupManager = $backupManager;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function processAll()
    {
        $this->processBlocks();
        $this->processPages();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function processBlocks($ids = [])
    {
        $this->processByType(ContentVersionInterface::TYPE_BLOCK, $ids);
    }

    /**
     * @inheritDoc
     */
    public function processPages($ids = [])
    {
        $this->processByType(ContentVersionInterface::TYPE_PAGE, $ids);
    }

    /**
     * @inheritDoc
     */
    public function processFile(string $filePath)
    {
        $count = 0;
        try {
            switch ($type = $this->defineTypeEntityFromFile($filePath)) {
                case ContentVersionInterface::TYPE_PAGE:
                    $configItems = $this->getConfigItems($this->pagesConfigReader, [], $filePath);
                    break;

                case ContentVersionInterface::TYPE_BLOCK:
                    $configItems = $this->getConfigItems($this->blockConfigReader, [], $filePath);
                    break;

                default:
                    return $count;
            }

            $count = $this->importItems($configItems, $type);
        } catch (\Exception $e) {
            $this->handleException($type, $e);
        }

        return $count;
    }

    /**
     * @param int $cmsType
     * @param array $ids
     * @throws InvalidXmlImportFilesException
     * @throws LocalizedException
     * @return $this
     */
    private function processByType(int $cmsType, array $ids = [])
    {
        try {
            $reader = ($cmsType === ContentVersionInterface::TYPE_BLOCK)
                ? $this->blockConfigReader : $this->pagesConfigReader;
            $configItems = $this->getConfigItems($reader, $ids);

            $this->importItems($configItems, $cmsType);
        } catch (\Exception $e) {
            $this->handleException($cmsType, $e, true);
        }
        return $this;
    }

    /**
     * @param array $configItems
     * @param int $type
     * @return int
     */
    private function importItems(array $configItems, int $type)
    {
        $count = 0;
        foreach ($configItems as $configItem) {
            $this->currentImportItem = $configItem;
            if ($contentVersion = $this->matchContentVersion(
                $configItem[ContentVersionInterface::IDENTIFIER],
                $type,
                $configItem[ContentVersionInterface::STORE_IDS])
            ) {
                $this->updateVersion($contentVersion, $configItem);
            } else {
                $this->createVersion($type, $configItem);
            }
            $count++;
        }

        $this->currentImportItem = [];

        return $count;
    }

    /**
     * @param int $cmsType
     * @param \Exception $exception
     * @param bool $toLog
     * @return void
     * @throws InvalidXmlImportFilesException
     * @throws LocalizedException
     */
    private function handleException(int $cmsType, \Exception $exception, $toLog = false)
    {
        if ($exception instanceof InvalidXmlImportFilesException
            || empty($this->currentImportItem)) {
            throw $exception;
        }

        $errorMessage = sprintf(
            "%s import with identifier - %s for store id - %s was failed! \n %s.",
            ($cmsType === ContentVersionInterface::TYPE_BLOCK) ? 'Block' : 'Page',
            $this->currentImportItem[ContentVersionInterface::IDENTIFIER],
            $this->currentImportItem[ContentVersionInterface::STORE_IDS] ?: '0',
            $exception->getMessage()
        );

        if ($toLog) {
            $this->clearCurrentItemContent($cmsType);
            $this->logger->critical(
                sprintf("%s \n Issue with import item: %s",
                $errorMessage,
                print_r($this->currentImportItem, true)
                )
            );
        }

        throw new LocalizedException(__($errorMessage));
    }

    /**
     * @inheritDoc
     */
    public function updateVersion($contentVersion, $configItem)
    {
        if (version_compare($contentVersion->getVersion(), $configItem[ContentVersionInterface::VERSION], '<')) {
            $storeIdsData = $configItem['store_ids'] ?? 0;
            $configItem['store_ids'] = $this->prepareStoreIds($storeIdsData);

            /* Update cms block/page or create if not exist */
            $this->updateCmsEntity($contentVersion->getType(), $configItem);

            /* Update content version */
            $contentVersion
                ->setVersion($configItem[ContentVersionInterface::VERSION])
                ->setStoreIds($storeIdsData);
            $this->contentVersionRepository->save($contentVersion);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function createVersion($type, $data)
    {
        $version = $data[ContentVersionInterface::VERSION]
            ?? self::DEFAULT_VERSION;
        $storeIdsData = $data['store_ids'] ?? 0;
        $data['store_ids'] = $this->prepareStoreIds($storeIdsData);

        /* Create CMS-block */
        $this->updateCmsEntity($type, $data);

        /* Create Content Version */
        $dataModel = $this->contentVersionFactory->create()
            ->setType($type)
            ->setIdentifier($data[ContentVersionInterface::IDENTIFIER])
            ->setVersion($version)
            ->setStoreIds($storeIdsData);
        $this->contentVersionRepository->save($dataModel);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentVersion(string $id, int $type, ?string $storeIds)
    {
        if ($storeIds !== null && $versionModel = $this->matchContentVersion($id, $type, $storeIds)) {
            return $versionModel->getVersion();
        }

        return self::DEFAULT_VERSION;
    }

    /**
     * @inheritDoc
     */
    public function deleteContentVersion(string $id, int $type, array $storeIds)
    {
        $versionModel = $this->matchContentVersion($id, $type, implode(',', $storeIds));

        return $versionModel && $this->contentVersionRepository->delete($versionModel);
    }

    /**
     * @param string $file
     * @return int|null
     */
    private function defineTypeEntityFromFile(string $file)
    {
        if (strpos(
            file_get_contents($file, false, null, 0, self::XML_FILE_HEADER_LENGTH),
            CmsEntityGeneratorInterface::PAGE_SCHEMA_NAME)
        ) {
            return ContentVersionInterface::TYPE_PAGE;
        } elseif (strpos(
            file_get_contents($file, false, null, 0, self::XML_FILE_HEADER_LENGTH),
            CmsEntityGeneratorInterface::BLOCK_SCHEMA_NAME))
        {
            return ContentVersionInterface::TYPE_BLOCK;
        }

        return null;
    }

    /**
     * Updates existing cms-block/-page content,title or creates new one if not exists
     *
     * @param $type
     * @param $data
     * @return $this
     * @throws \Exception
     */
    private function updateCmsEntity($type, $data)
    {
        $repository = $this->getCmsRepository($type);
        $factory = $this->getCmsFactory($type);
        /* Check if cms-block/-page exists */
        $searchCriteria = $this->prepareSearchCriteria($data);
        $items = $repository->getList($searchCriteria)->getItems();
        /* Block or page exists */
        if (count($items)) {
            $cmsDataModel = array_shift($items);
            /* Create backup of cms-block/-page */
            $this->backupManager->createBackup(
                $this->resolveBackupType($type),
                $cmsDataModel
            );
        } else { /* Create new block or page */
            get_class($factory); // FIX: "PHP Fatal error:  Uncaught Error: Call to a member function create() on null"
            $cmsDataModel = $factory->create()
                ->setIdentifier($data['identifier']);
        }

        $cmsDataModel
            ->setData('title', $data['title'])
            ->setData('content', $data['content'])
            ->setData('store_id', $data['store_ids']);

        if (isset($data['is_active'])) {
            $cmsDataModel->setData('is_active', $data['is_active']);
        }

        if (isset($data['content_heading'])) {
            $cmsDataModel->setData('content_heading', $data['content_heading']);
        }
        if (isset($data['page_layout'])) {
            $cmsDataModel->setData('page_layout', $data['page_layout']);
        }

        $repository->save($cmsDataModel);

        return $this;
    }

    /**
     * Retrieves all data from block/page xml config, filtered by provided identifiers
     *
     * @param ReaderAbstract $configReader
     * @param array $filterIds
     * @param null $file
     * @return array
     * @throws LocalizedException
     */
    private function getConfigItems(ReaderAbstract $configReader, $filterIds = [], $file = null)
    {
        if ($file) {
            $config = $configReader->readFromFile($file);
        } else {
            $config = $configReader->read();
        }

        if (empty($filterIds)) {
            return $config;
        }

        $filteredConfig = [];
        if (!empty($filterIds)) {
            foreach ($config as $index => $item) {
                if (in_array($item['identifier'], $filterIds)) {
                    $filteredConfig[$index] = $item;
                }
            }
        }

        return $filteredConfig;
    }

    /**
     * Retrieves all CMS content records from DB based on type (0 - blocks, 1-pages), filtered by identifiers
     *
     * @param $type
     * @param array $filterIds
     * @return array
     */
    private function getContentVersions($type, $filterIds = [])
    {
        $result = [];
        $filtersGroups = [];

        $filterType = $this->filterBuilder
            ->setField(ContentVersionInterface::TYPE)
            ->setConditionType('eq')
            ->setValue($type)
            ->create();
        $filterGroup1 = $this->filterGroupBuilder
            ->setFilters([$filterType])
            ->create();
        $filtersGroups[] = $filterGroup1;

        if (!empty($filterIds)) {
            $filterId = $this->filterBuilder
                ->setField(ContentVersionInterface::IDENTIFIER)
                ->setConditionType('in')
                ->setValue(implode(",", $filterIds))
                ->create();
            $filterGroup2 = $this->filterGroupBuilder
                ->setFilters([$filterId])
                ->create();
            $filtersGroups[] = $filterGroup2;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups($filtersGroups)
            ->create();

        try {
            $result = $this->contentVersionRepository->getList($searchCriteria)->getItemsArray();
        } catch (LocalizedException $e) {
            $this->logger->critical(__('Something went wrong during getting content versions'));
        }

        return $result;
    }

    /**
     * Prepare store ids array for cms-block or cms-page
     *
     * @param string $storeIdsString
     * @return array
     */
    private function prepareStoreIds(string $storeIdsString)
    {
        if (empty($storeIdsString)) {
            return ['0'];
        } else {
            $storeIds = explode(',', $storeIdsString);
            if (in_array('0', $storeIds)) {
                $storeIds = ['0'];
            }
        }

        return $storeIds;
    }

    /**
     * Prepare search criteria to find matching cms-block or cms-page
     *
     * @param $data
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function prepareSearchCriteria($data)
    {
        $filtersGroups = [];

        $filterIdentifier = $this->filterBuilder
            ->setField('identifier')
            ->setConditionType('eq')
            ->setValue($data['identifier'])
            ->create();
        $filterGroup1 = $this->filterGroupBuilder
            ->setFilters([$filterIdentifier])
            ->create();
        $filtersGroups[] = $filterGroup1;

        $filterStore = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('in')
            ->setValue($data['store_ids'])
            ->create();
        $filterGroup2 = $this->filterGroupBuilder
            ->setFilters([$filterStore])
            ->create();
        $filtersGroups[] = $filterGroup2;

        return $this->searchCriteriaBuilder
            ->setFilterGroups($filtersGroups)
            ->create();
    }

    /**
     * Retrieve factory object for cms-block or cms-page
     *
     * @param $strategy
     * @return BlockInterfaceFactory|PageInterfaceFactory
     */
    private function getCmsFactory($strategy)
    {
        if ($strategy === ContentVersionInterface::TYPE_BLOCK) {
            return $this->blockInterfaceFactory;
        } elseif ($strategy === ContentVersionInterface::TYPE_PAGE) {
            return $this->pageInterfaceFactory;
        }
    }

    /**
     * Retrieve repository for cms-block or cms-page
     *
     * @param $strategy
     * @return BlockRepositoryInterface|PageRepositoryInterface
     */
    private function getCmsRepository($strategy)
    {
        if ((int)$strategy === ContentVersionInterface::TYPE_BLOCK) {
            return $this->blockRepository;
        } elseif ((int)$strategy === ContentVersionInterface::TYPE_PAGE) {
            return $this->pageRepository;
        }
    }

    /**
     * Retrieve strategy for BackupManager
     *
     * @param $strategy
     * @return string
     */
    private function resolveBackupType($strategy)
    {
        if ((int)$strategy === ContentVersionInterface::TYPE_BLOCK) {
            return BackupManager::TYPE_CMS_BLOCK;
        } elseif ((int)$strategy === ContentVersionInterface::TYPE_PAGE) {
            return BackupManager::TYPE_CMS_PAGE;
        }
    }

    /**
     * @param string $identifier
     * @param int $type
     * @param string|null $storeIds
     * @return ContentVersionInterface|null
     */
    private function matchContentVersion(string $identifier, int $type, ?string $storeIds)
    {
        $searchStoreIdsArr = explode(',', $storeIds ?: '0');
        $contentVersions = $this->getContentVersions($type, [$identifier]);
        foreach ($contentVersions as $contentVersion) {
            if (count(array_intersect(explode(',', $contentVersion->getStoreIds()), $searchStoreIdsArr))) {
                return $contentVersion;
            }
        }

        return null;
    }

    /**
     * @param int $cmsType
     * @return void
     */
    private function clearCurrentItemContent(int $cmsType)
    {
        $contentKey = ($cmsType === ContentVersionInterface::TYPE_BLOCK)
            ? PageInterface::CONTENT : BlockInterface::CONTENT;
        unset($this->currentImportItem[$contentKey]);
    }
}
