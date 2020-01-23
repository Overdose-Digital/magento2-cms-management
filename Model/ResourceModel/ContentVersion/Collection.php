<?php

namespace Overdose\CMSContent\Model\ResourceModel\ContentVersion;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Overdose\CMSContent\Model\ContentVersion::class,
            \Overdose\CMSContent\Model\ResourceModel\ContentVersion::class
        );
    }
}
