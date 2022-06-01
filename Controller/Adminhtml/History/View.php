<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Controller\Adminhtml\History;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class View extends Action implements HttpGetActionInterface
{
    /**
     * Execute view action
     *
     * @return ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->prepend(__('Backup Preview'));

        return $resultPage;
    }
}
