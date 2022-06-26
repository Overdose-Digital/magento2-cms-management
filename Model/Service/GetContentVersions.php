<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Service;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\ContentVersionRepositoryInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Model\ContentVersion;
use Psr\Log\LoggerInterface;

/**
 * Class GetContentVersions
 */
class GetContentVersions
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var ContentVersionRepositoryInterface
     */
    private $contentVersionRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param ContentVersionRepositoryInterface $contentVersionRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        ContentVersionRepositoryInterface $contentVersionRepository,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->contentVersionRepository = $contentVersionRepository;
        $this->logger = $logger;
    }

    /**
     * Retrieves all CMS content records from DB based on type (0 - blocks, 1-pages), filtered by identifiers
     *
     * @param int $type
     * @param array $filterIds
     *
     * @return array
     */
    public function execute(int $type, array $filterIds): array
    {
        $result = [];

        try {
            $searchCriteria = $this->prepareSearchCriteria($type, $filterIds);

            $searchResult = $this->contentVersionRepository->getList($searchCriteria)->getItems();

            /** @var ContentVersion $item */
            foreach ($searchResult as $item) {
                $storePart = str_replace(',', '_', $item->getStoreIds());
                $key = $item->getIdentifier() . '_' . $storePart;
                $result[$key] = $item;
            }
            return $result;
        } catch (LocalizedException $e) {
            $this->logger->critical(__('Something went wrong during getting content versions'));
        }
        return $result;
    }

    /**
     * Prepare search criteria
     *
     * @param int $type
     * @param array $filterIds
     *
     * @return SearchCriteria
     */
    private function prepareSearchCriteria(int $type, array $filterIds): SearchCriteria
    {
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

        if (count($filterIds)) {
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

        return $this->searchCriteriaBuilder
            ->setFilterGroups($filtersGroups)
            ->create();
    }
}
