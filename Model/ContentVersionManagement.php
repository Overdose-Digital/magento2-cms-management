<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Exception;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;
use Overdose\CMSContent\Api\ContentVersionRepositoryInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterfaceFactory;
use Overdose\CMSContent\Api\StoreManagementInterface;
use Overdose\CMSContent\Exception\InvalidXmlImportFilesException;
use Overdose\CMSContent\Model\Config\Block\Reader as BlocksConfigReader;
use Overdose\CMSContent\Model\Config\Page\Reader as PagesConfigReader;
use Overdose\CMSContent\Model\Config\ReaderAbstract;
use Overdose\CMSContent\Model\Content\Generator\CmsEntityGeneratorInterface;
use Overdose\CMSContent\Model\Service\GetCmsEntityItems;
use Overdose\CMSContent\Model\Service\GetContentVersions;
use Psr\Log\LoggerInterface;

class ContentVersionManagement implements ContentVersionManagementInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $currentImportItem = [];

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
     * @var BackupManager
     */
    private $backupManager;

    /**
     * @var GetContentVersions
     */
    private $getContentVersions;

    /**
     * @var GetCmsEntityItems
     */
    private $getCmsEntityItems;

    /**
     * @var EntityManagement
     */
    private $entityManagement;

    /**
     * @var StoreManagementInterface
     */
    private $storeManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ContentVersionManagement constructor.
     *
     * @param Config $config
     * @param ContentVersionInterfaceFactory $contentVersionFactory
     * @param ContentVersionRepositoryInterface $contentVersionRepository
     * @param BlocksConfigReader $blockConfigReader
     * @param PagesConfigReader $pagesConfigReader
     * @param BackupManager $backupManager
     * @param EntityManagement $entityManagement
     * @param GetContentVersions $getContentVersions
     * @param GetCmsEntityItems $getCmsEntityItems
     * @param StoreManagementInterface $storeManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        ContentVersionInterfaceFactory $contentVersionFactory,
        ContentVersionRepositoryInterface $contentVersionRepository,
        BlocksConfigReader $blockConfigReader,
        PagesConfigReader $pagesConfigReader,
        BackupManager $backupManager,
        EntityManagement $entityManagement,
        GetContentVersions $getContentVersions,
        GetCmsEntityItems $getCmsEntityItems,
        StoreManagementInterface $storeManagement,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionRepository = $contentVersionRepository;
        $this->blockConfigReader = $blockConfigReader;
        $this->pagesConfigReader = $pagesConfigReader;
        $this->backupManager = $backupManager;
        $this->entityManagement = $entityManagement;
        $this->getContentVersions = $getContentVersions;
        $this->getCmsEntityItems = $getCmsEntityItems;
        $this->storeManagement = $storeManagement;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function processAll(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $this->processBlocks();
        $this->processPages();
    }

    /**
     * @inheritDoc
     */
    public function processBlocks(array $ids = []): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        try {
            $this->processByType(ContentVersionInterface::TYPE_BLOCK, $ids);
        } catch (InvalidXmlImportFilesException | LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function processPages(array $ids = []): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        try {
            $this->processByType(ContentVersionInterface::TYPE_PAGE, $ids);
        } catch (InvalidXmlImportFilesException | LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function processFile(string $filePath): int
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
        } catch (Exception $e) {
            $this->handleException($type, $e);
        }

        return $count;
    }

    /**
     * @param int $cmsType
     * @param array $ids
     *
     * @throws InvalidXmlImportFilesException
     * @throws LocalizedException
     * @return ContentVersionManagementInterface
     */
    private function processByType(int $cmsType, array $ids = []): ContentVersionManagementInterface
    {
        try {
            $reader = ($cmsType === ContentVersionInterface::TYPE_BLOCK)
                ? $this->blockConfigReader : $this->pagesConfigReader;
            $configItems = $this->getConfigItems($reader, $ids);

            $this->importItems($configItems, $cmsType);
        } catch (Exception $e) {
            $this->handleException($cmsType, $e, true);
        }
        return $this;
    }

    /**
     * @param array $configItems
     * @param int $type
     *
     * @return int
     * @throws LocalizedException
     */
    private function importItems(array $configItems, int $type): int
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
     * @param Exception $exception
     * @param bool $toLog
     * @return void
     * @throws InvalidXmlImportFilesException
     * @throws LocalizedException
     */
    private function handleException(int $cmsType, Exception $exception, $toLog = false)
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
    public function updateVersion($contentVersion, $configItem): ContentVersionManagementInterface
    {
        if (version_compare($contentVersion->getVersion(), $configItem[ContentVersionInterface::VERSION], '<')) {
            $storeIdsData = $configItem['store_ids'] ?? 0;
            $configItem['store_ids'] = $this->prepareStoreIds((string)$storeIdsData);

            /* Update cms block/page or create if not exist */
            $this->updateCmsEntity((int)$contentVersion->getType(), $configItem);

            /* Update content version */
            $contentVersion
                ->setVersion($configItem[ContentVersionInterface::VERSION])
                ->setStoreIds((string)$storeIdsData);
            $this->contentVersionRepository->save($contentVersion);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function createVersion(int $type, array $data): ContentVersionManagementInterface
    {
        $version = $data[ContentVersionInterface::VERSION]
            ?? self::DEFAULT_VERSION;
        $storeIdsData = $data['store_ids'] ?? 0;
        $data['store_ids'] = $this->prepareStoreIds((string)$storeIdsData);

        /* Create CMS-block */
        $this->updateCmsEntity($type, $data);

        /* Create Content Version */
        $dataModel = $this->contentVersionFactory->create()
            ->setType((string)$type)
            ->setIdentifier((string)$data[ContentVersionInterface::IDENTIFIER])
            ->setVersion($version)
            ->setStoreIds((string)$storeIdsData);

        $this->contentVersionRepository->save($dataModel);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentVersion(string $id, int $type, ?string $storeIds): string
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
    private function defineTypeEntityFromFile(string $file): ?int
    {
        if (strpos(
            file_get_contents($file, false, null, 0, self::XML_FILE_HEADER_LENGTH),
            CmsEntityGeneratorInterface::PAGE_SCHEMA_NAME)
        ) {
            return ContentVersionInterface::TYPE_PAGE;
        } elseif (strpos(
            file_get_contents($file, false, null, 0, self::XML_FILE_HEADER_LENGTH),
            CmsEntityGeneratorInterface::BLOCK_SCHEMA_NAME)
        ) {
            return ContentVersionInterface::TYPE_BLOCK;
        }

        return null;
    }

    /**
     * Updates existing cms-block/-page content,title or creates new one if not exists
     *
     * @param int $type
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    private function updateCmsEntity(int $type, array $data): void
    {
        $type = ($type === ContentVersionInterface::TYPE_BLOCK)
            ? EntityManagement::TYPE_BLOCK : EntityManagement::TYPE_PAGE;

        /* Check if cms-block/-page exists */
        $items = $this->getCmsEntityItems->execute($type, $data['identifier'], $data['store_ids']);
        /* Block or page exists */
        if (count($items)) {
            $cmsDataModel = array_shift($items);
            $this->backupManager->createBackup(
                $this->resolveBackupType($type),
                $cmsDataModel
            );
        } else {
            $cmsDataModel = $this->entityManagement->getFactory($type);

            $cmsDataModel->setIdentifier($data['identifier']);
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

        $this->entityManagement->getRepository($type)->save($cmsDataModel);
    }

    /**
     * Retrieves all data from block/page xml config, filtered by provided identifiers
     *
     * @param ReaderAbstract $configReader
     * @param array $filterIds
     * @param null $file
     *
     * @return array
     * @throws LocalizedException
     */
    private function getConfigItems(ReaderAbstract $configReader, array $filterIds = [], $file = null): array
    {
        if ($file) {
            $config = $configReader->readFromFile($file);
        } else {
            $config = $configReader->read();
        }

        if (!count($filterIds)) {
            return $config;
        } else {
            $filteredConfig = [];
            foreach ($config as $index => $item) {
                if (in_array($item['identifier'], $filterIds)) {
                    $filteredConfig[$index] = $item;
                }
            }
        }
        return $filteredConfig;
    }

    /**
     * Prepare store ids array for cms-block or cms-page
     *
     * @param string $storeIdsString
     *
     * @return array
     */
    private function prepareStoreIds(string $storeIdsString): array
    {
        if (empty($storeIdsString)) {
            return ['0'];
        } else {
            $storeIds = explode(',', $storeIdsString);
            $storeIds = $this->storeManagement->filterStoresByStoreIds($storeIds);

            if (in_array('0', $storeIds)) {
                $storeIds = ['0'];
            }
        }
        return $storeIds;
    }

    /**
     * Retrieve strategy for BackupManager
     *
     * @param $strategy
     *
     * @return string
     */
    private function resolveBackupType($strategy): string
    {
        if ((int)$strategy === ContentVersionInterface::TYPE_BLOCK) {
            return BackupManager::TYPE_CMS_BLOCK;
        }
        return BackupManager::TYPE_CMS_PAGE;
    }

    /**
     * @param string $identifier
     * @param int $type
     * @param string|null $storeIds
     *
     * @return ContentVersionInterface|null
     */
    private function matchContentVersion(string $identifier, int $type, ?string $storeIds): ?ContentVersionInterface
    {
        $searchStoreIdsArr = explode(',', $storeIds ?: '0');
        $contentVersions = $this->getContentVersions->execute($type, [$identifier]);
        foreach ($contentVersions as $contentVersion) {
            if (count(array_intersect(explode(',', $contentVersion->getStoreIds()), $searchStoreIdsArr))) {
                return $contentVersion;
            }
        }
        return null;
    }

    /**
     * @param int $cmsType
     *
     * @return void
     */
    private function clearCurrentItemContent(int $cmsType)
    {
        $contentKey = ($cmsType === ContentVersionInterface::TYPE_BLOCK)
            ? PageInterface::CONTENT : BlockInterface::CONTENT;
        unset($this->currentImportItem[$contentKey]);
    }
}
