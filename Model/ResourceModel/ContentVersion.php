<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;

class ContentVersion extends AbstractDb
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
    protected function _beforeSave(AbstractModel $object)
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
