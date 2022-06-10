<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Api\Filter;

class Import extends AbstractDataProvider
{
    /**
     * We can provide prefill data this way
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function addFilter(Filter $filter)
    {
        return $this;
    }
}
