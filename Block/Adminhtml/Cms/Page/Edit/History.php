<?php

namespace Overdose\CMSContent\Block\Adminhtml\Cms\Page\Edit;

use Overdose\CMSContent\Model\BackupManager;

class History extends  \Overdose\CMSContent\Block\Adminhtml\Cms\CmsAbstract
{
    protected $urlParamId = 'page_id';
    protected $bcType = BackupManager::TYPE_CMS_PAGE;
}
