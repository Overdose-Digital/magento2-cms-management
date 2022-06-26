<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Service;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Model\EntityManagement;

/**
 * Class GetCmsEntityItems
 */
class GetCmsEntityItems
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
     * @var EntityManagement
     */
    private $entityManagement;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param EntityManagement $entityManagement
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        EntityManagement $entityManagement
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->entityManagement = $entityManagement;
    }

    /**
     * Get Cms items
     *
     * /**
     * @param string $type
     * @param string $identifier
     * @param array $storeIds
     *
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $type, string $identifier, array $storeIds): array
    {
        $repository = $this->getCmsRepository($type);

        $searchCriteria = $this->prepareSearchCriteria($identifier, $storeIds);

        return $repository->getList($searchCriteria)->getItems();
    }

    /**
     * Retrieve repository for cms-block or cms-page
     *
     * @param string $type
     *
     * @return BlockRepositoryInterface|PageRepositoryInterface
     * @throws LocalizedException
     */
    private function getCmsRepository(string $type)
    {
        return $this->entityManagement->getRepository($type);
    }

    /**
     * Prepare search criteria
     *
     * @param string $identifier
     * @param array $storeIds
     *
     * @return SearchCriteria
     */
    private function prepareSearchCriteria(string $identifier, array $storeIds): SearchCriteria
    {
        $filtersGroups = [];

        $filterIdentifier = $this->filterBuilder
            ->setField('identifier')
            ->setConditionType('eq')
            ->setValue($identifier)
            ->create();
        $filterGroup1 = $this->filterGroupBuilder
            ->setFilters([$filterIdentifier])
            ->create();
        $filtersGroups[] = $filterGroup1;

        $filterStore = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('in')
            ->setValue($storeIds)
            ->create();
        $filterGroup2 = $this->filterGroupBuilder
            ->setFilters([$filterStore])
            ->create();
        $filtersGroups[] = $filterGroup2;

        return $this->searchCriteriaBuilder
            ->setFilterGroups($filtersGroups)
            ->create();
    }
}
