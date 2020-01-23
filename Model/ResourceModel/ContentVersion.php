<?php

namespace Overdose\CMSContent\Model\ResourceModel;

use Overdose\CMSContent\Api\Data\ContentVersionInterface;

class ContentVersion extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('od_cmscontent_version', 'id');
    }

    /**
     * @inheritDoc
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (empty($storeIds = $object->getData(ContentVersionInterface::STORE_IDS))) {
                $storeIds = '0';
        } else {
            $storeIdsArray = explode(',', $storeIds);
            if (in_array('0', $storeIdsArray)) {
                $storeIds = '0';
            }
        }

        $object->setData(ContentVersionInterface::STORE_IDS, $storeIds);

        return parent::_beforeSave($object);
    }
}
