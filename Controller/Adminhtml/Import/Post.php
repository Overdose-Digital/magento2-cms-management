<?php

namespace Overdose\CMSContent\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\File\UploaderFactory;
use Overdose\CMSContent\Api\ContentImportInterface;
use Overdose\CMSContent\Model\Filesystem;

class Post extends Action
{
    protected $uploaderFactory;

    protected $importExportInterface;

    protected $filesystem;

    protected $redirectFactory;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        ContentImportInterface $importExportInterface,
        RedirectFactory $redirectFactory,
        Filesystem $filesystem
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->importExportInterface = $importExportInterface;
        $this->filesystem = $filesystem;
        $this->redirectFactory = $redirectFactory;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Overdose_CMSContent::import');
    }

    public function execute()
    {
        try {
            $cmsMode = $this->getRequest()->getParam('cms_mode');
            $mediaMode = $this->getRequest()->getParam('media_mode');

            $destinationPath = $this->filesystem->getUploadPath();

            $uploader = $this->uploaderFactory->create(['fileId' => 'zipfile']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationPath);

            $zipFile = $result['path'] . $result['file'];

            $this->importExportInterface
                ->setCmsModeOption($cmsMode)
                ->setMediaModeOption($mediaMode);

            $count = $this->importExportInterface->importContentFromZipFile($zipFile, true);

            $this->messageManager->addSuccessMessage(__('A total of %1 item(s) have been imported/updated.', $count));

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}
