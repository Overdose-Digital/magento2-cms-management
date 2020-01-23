<?php

namespace Overdose\CMSContent\Block\Adminhtml\Cms\Block\Edit;

use Overdose\CMSContent\Model\BackupManager;

class History extends \Overdose\CMSContent\Block\Adminhtml\Cms\CmsAbstract
{
    protected $urlParamId = 'block_id';
    protected $bcType = BackupManager::TYPE_CMS_BLOCK;
}
