<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\ResourceModel\ContentVersion;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Overdose\CMSContent\Model\ContentVersion;
use Overdose\CMSContent\Model\ResourceModel\ContentVersion as ResourceContentVersion;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ContentVersion::class, ResourceContentVersion::class);
    }
}
