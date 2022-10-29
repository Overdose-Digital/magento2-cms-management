<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Controller\Adminhtml\History;

use Laminas\Json\Json;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Cms\Model\Page;
use Overdose\CMSContent\Api\ContentImportInterface;
use Overdose\CMSContent\File\FileManagerInterface;
use Overdose\CMSContent\Model\BackupManager;

class Apply extends Action implements HttpGetActionInterface
{
    /**
     * @var ContentImportInterface
     */
    protected $importExportInterface;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var BackupManager
     */
    private $backupManager;
    /**
     * @var FileManagerInterface
     */
    private $file;
    /**
     * @var Json
     */
    private $jsonFormatter;

    /**
     * @param Context $context
     * @param ContentImportInterface $importExportInterface
     * @param RedirectFactory $redirectFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param PageRepositoryInterface $pageRepository
     * @param BackupManager $backupManager
     * @param FileManagerInterface $file
     * @param Json $jsonFormatter
     */
    public function __construct(
        Action\Context           $context,
        ContentImportInterface   $importExportInterface,
        RedirectFactory          $redirectFactory,
        BlockRepositoryInterface $blockRepository,
        PageRepositoryInterface  $pageRepository,
        BackupManager            $backupManager,
        FileManagerInterface     $file,
        Json                     $jsonFormatter
    ) {
        $this->importExportInterface = $importExportInterface;
        $this->redirectFactory = $redirectFactory;
        $this->pageRepository = $pageRepository;
        $this->blockRepository = $blockRepository;
        $this->backupManager = $backupManager;
        $this->file = $file;
        $this->jsonFormatter = $jsonFormatter;

        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $identifier = $this->getRequest()->getParam('bc_identifier');
        $itemId  = $this->getRequest()->getParam('item_id');
        $file    = $this->getRequest()->getParam('item');
        $cmsType = $this->getRequest()->getParam('bc_type');
        $storeId = $this->getRequest()->getParam('store_id');
        try {
            if (!is_null($storeId)) {
                $path = $this->backupManager->getBackupPathByStoreId($cmsType, $identifier, (int)$storeId) . DIRECTORY_SEPARATOR . $file;
            } else {
                $path = $this->backupManager->getBackupPath($cmsType, $identifier) . DIRECTORY_SEPARATOR . $file;
            }
            $backupFile = $this->file->readData($path);
            $jsonBackup = $this->jsonFormatter->decode($backupFile, true);
            if ($cmsType == BackupManager::TYPE_CMS_BLOCK) {
                $cmsObject = $this->blockRepository->getById($itemId);
                $cmsObject->setContent($jsonBackup['content']);
                $this->blockRepository->save($cmsObject);
            } else if ($cmsType == BackupManager::TYPE_CMS_PAGE) {
                $cmsObject = $this->pageRepository->getById($itemId);
                $cmsObject->setContent($jsonBackup['content']);
                $this->pageRepository->save($cmsObject);
            }
            $this->messageManager->addSuccessMessage(__('Successfully changed content with backup file %1', $file));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong with saving block or page %1', $file));
        }
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
