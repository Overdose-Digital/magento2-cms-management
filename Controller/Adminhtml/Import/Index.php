<?php

namespace Overdose\CMSContent\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Overdose_CMSContent::import';


    protected $pageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Overdose_CMSContent::import')
            ->addBreadcrumb(__('CMS'), __('CMS'));

        $resultPage->addBreadcrumb(__('Import CMS'), __('Import CMS'));

        $resultPage->getConfig()->getTitle()->prepend(__('CMS'));
        $resultPage->getConfig()->getTitle()->prepend(__('CMS Import'));

        return $resultPage;
    }
}
