<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Overdose\CMSContent\Api\ContentImportInterface;

class Import extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Overdose_CMSContent::import';

    /**
     * @var ContentImportInterface
     */
    protected $importExportInterface;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @param Action\Context $context
     * @param ContentImportInterface $importExportInterface
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Action\Context $context,
        ContentImportInterface $importExportInterface,
        RedirectFactory $redirectFactory
    ) {
        $this->importExportInterface = $importExportInterface;
        $this->redirectFactory = $redirectFactory;

        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $cmsMode    = $this->getRequest()->getParam('cms_import_mode');
            $mediaMode  = $this->getRequest()->getParam('media_import_mode');
            $upload     = $this->getRequest()->getParam('upload');

            $count = $this->importExportInterface
                ->setCmsModeOption($cmsMode)
                ->setMediaModeOption($mediaMode)
                ->importContentFromZipFile(
                    $upload[0]['path'] . DIRECTORY_SEPARATOR . $upload[0]['file'],
                    true
                );

            $this->messageManager->addSuccessMessage(
                __('A total of %1 item(s) have been imported/updated.', $count)
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}
