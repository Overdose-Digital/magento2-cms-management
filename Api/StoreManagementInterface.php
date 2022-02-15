<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Api;

interface StoreManagementInterface
{
    /**
     * @param array $storeCodes
     * @return array
     */
    public function getStoreIdsByCodes(array $storeCodes): array;
}
