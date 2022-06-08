<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion\CollectionFactory as ContentVersionCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Overdose\CMSContent\Api\ContentVersionRepositoryInterface;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion as ResourceContentVersion;
use Magento\Framework\Exception\NoSuchEntityException;

class ContentVersionRepository implements ContentVersionRepositoryInterface
{
    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ResourceContentVersion
     */
    private $resource;

    /**
     * @var ContentVersionCollectionFactory
     */
    private $contentVersionCollectionFactory;

    /**
     * @var ContentVersionFactory
     */
    private $contentVersionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * ContentVersionRepository constructor
     *
     * @param ResourceContentVersion $resource
     * @param ContentVersionFactory $contentVersionFactory
     * @param ContentVersionCollectionFactory $contentVersionCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceContentVersion $resource,
        ContentVersionFactory $contentVersionFactory,
        ContentVersionCollectionFactory $contentVersionCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionCollectionFactory = $contentVersionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(ContentVersionInterface $contentVersion): ContentVersionInterface
    {
        try {
            $this->resource->save($contentVersion);

            return $this->get($contentVersion->getId());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the contentVersion: %1', $exception->getMessage())
            );
        }
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
        return $contentVersion;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->contentVersionCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
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
