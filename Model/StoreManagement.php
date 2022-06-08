<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Overdose\CMSContent\Api\StoreManagementInterface;

class StoreManagement implements StoreManagementInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepositoryInterface;

    /**
     * @param StoreRepositoryInterface $storeRepositoryInterface
     */
    public function __construct(StoreRepositoryInterface $storeRepositoryInterface)
    {
        $this->storeRepositoryInterface = $storeRepositoryInterface;
    }

    /**
     * Get store ids by codes
     * @param array $storeCodes
     * @return array
     */
    public function getStoreIdsByCodes(array $storeCodes): array
    {
        $storeCodes = $this->filterStoresByStoreCodes($storeCodes);
        $storeIds = [];
        foreach ($storeCodes as $storeCode) {
            if ($storeCode == 'admin') {
                $storeIds[] = 0;
            } else {
                try {
                    $store = $this->storeRepositoryInterface->get($storeCode);
                    if ($store && $store->getId()) {
                        $storeIds[] = $store->getId();
                    }
                } catch (NoSuchEntityException $exception) {
                    continue;
                }
            }
        }
        return $storeIds;
    }

    /**
     * Filter stores passed to import by existed
     *
     * @param array $storeCodes
     *
     * @return array
     */
    public function filterStoresByStoreCodes(array $storeCodes): array
    {
        $filteredStores = [];
        $allStores = $this->storeRepositoryInterface->getList();
        foreach ($storeCodes as $storeCode) {
            if (array_key_exists($storeCode, $allStores)) {
                $filteredStores[] = $storeCode;
            }
        }
        return $filteredStores;
    }

    /**
     * Filter stores passed to import by existed
     *
     * @param array $storeIds
     *
     * @return array
     */
    public function filterStoresByStoreIds(array $storeIds): array
    {
        $filteredStores = [];
        $allStores = $this->storeRepositoryInterface->getList();
        foreach ($allStores as $store) {
            if (in_array($store->getId(), $storeIds)) {
                $filteredStores[] = $store->getId();
            }
        }
        return $filteredStores;
    }
}
