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
        $storeCodes = $this->filterStores($storeCodes);
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
     * @param array $storeCodes
     * @return array
     */
    private function filterStores(array $storeCodes): array
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
}
