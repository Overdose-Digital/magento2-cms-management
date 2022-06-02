<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Api\Data\ContentVersionSearchResultsInterface;
use Overdose\CMSContent\Api\Data\ContentVersionSearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion\CollectionFactory as ContentVersionCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Overdose\CMSContent\Api\ContentVersionRepositoryInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterfaceFactory;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion as ResourceContentVersion;
use Magento\Framework\Exception\NoSuchEntityException;

class ContentVersionRepository implements ContentVersionRepositoryInterface
{
    protected $dataObjectHelper;
    protected $searchResultsFactory;
    protected $dataObjectProcessor;
    protected $extensionAttributesJoinProcessor;
    protected $extensibleDataObjectConverter;
    protected $resource;
    protected $contentVersionCollectionFactory;
    protected $contentVersionFactory;
    protected $dataContentVersionFactory;

    private $collectionProcessor;
    private $storeManager;

    /**
     * ContentVersionRepository constructor
     *
     * @param ResourceContentVersion $resource
     * @param ContentVersionFactory $contentVersionFactory
     * @param ContentVersionInterfaceFactory $dataContentVersionFactory
     * @param ContentVersionCollectionFactory $contentVersionCollectionFactory
     * @param ContentVersionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceContentVersion $resource,
        ContentVersionFactory $contentVersionFactory,
        ContentVersionInterfaceFactory $dataContentVersionFactory,
        ContentVersionCollectionFactory $contentVersionCollectionFactory,
        ContentVersionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionCollectionFactory = $contentVersionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataContentVersionFactory = $dataContentVersionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @inheritdoc
     */
    public function save(ContentVersionInterface $contentVersion): ContentVersionInterface {
        /* if (empty($contentVersion->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $contentVersion->setStoreId($storeId);
        } */

        $contentVersionData = $this->extensibleDataObjectConverter->toNestedArray(
            $contentVersion,
            [],
            ContentVersionInterface::class
        );

        $contentVersionModel = $this->contentVersionFactory->create()->setData($contentVersionData);

        try {
            $this->resource->save($contentVersionModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the contentVersion: %1',
                $exception->getMessage()
            ));
        }
        return $contentVersionModel->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function get(string $id): ContentVersionInterface
    {
        $contentVersion = $this->contentVersionFactory->create();
        $this->resource->load($contentVersion, $id);
        if (!$contentVersion->getId()) {
            throw new NoSuchEntityException(__('content_version with id "%1" does not exist.', $id));
        }
        return $contentVersion->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ContentVersionSearchResultsInterface
    {
        $collection = $this->contentVersionCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            ContentVersionInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function delete(ContentVersionInterface $contentVersion): bool
    {
        try {
            $contentVersionModel = $this->contentVersionFactory->create();
            $this->resource->load($contentVersionModel, $contentVersion->getId());
            $this->resource->delete($contentVersionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the content_version: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id): bool
    {
        return $this->delete($this->get($id));
    }
}
