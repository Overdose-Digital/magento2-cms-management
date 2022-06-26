<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Block\Adminhtml\Cms\Page\Edit;

use Overdose\CMSContent\Block\Adminhtml\Cms\CmsAbstract;
use Overdose\CMSContent\Model\BackupManager;

class History extends CmsAbstract
{
    protected $urlParamId = 'page_id';
    protected $bcType = BackupManager::TYPE_CMS_PAGE;
}
