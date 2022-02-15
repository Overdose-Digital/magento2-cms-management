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
     * @var array
     */
    private $currentStores = [];

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

    private function init(): void
    {
        // @todo simplify this
        $stores = $this->storeRepositoryInterface->getList();
        foreach ($stores as $store) {
            $this->currentStores[$store->getCode()] = $store->getCode();
        }
    }

    /**
     * @param array $storeCodes
     * @return array
     */
    private function filterStores(array $storeCodes): array
    {
        if (empty($this->currentStores)) {
            $this->init();
        }
        $filteredStores = [];
        // @todo simplify this
        foreach ($storeCodes as $storeCode) {
            foreach ($this->currentStores as $to => $from) {
                if ($storeCode == $from) {
                    $filteredStores[] = $to;
                }
            }
        }

        return $filteredStores;
    }
}
