<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Block\Adminhtml\Cms\Block\Edit;

use Overdose\CMSContent\Block\Adminhtml\Cms\CmsAbstract;
use Overdose\CMSContent\Model\BackupManager;

class History extends CmsAbstract
{
    protected $urlParamId = 'block_id';
    protected $bcType = BackupManager::TYPE_CMS_BLOCK;
}
