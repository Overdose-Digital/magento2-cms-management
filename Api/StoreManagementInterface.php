<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Api;

interface StoreManagementInterface
{
    public function getStoreIdsByCodes(array $storeCodes): array;
}
