<?php

namespace Overdose\CMSContent\Controller\Adminhtml\Page;

use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Overdose\CMSContent\Api\ContentImportExportInterface;

class MassExport extends Action
{
    protected $filter;
    protected $collectionFactory;
    protected $importExportContentInterface;
    protected $fileFactory;
    protected $dateTime;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        ContentImportExportInterface $importExportContentInterface,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        DateTime $dateTime
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->importExportContentInterface = $importExportContentInterface;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Overdose_CMSContent::export_page');
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $pages = [];
        foreach ($collection as $page) {
            $pages[] = $page;
        }

        $fileType = $this->getFileType();

        $fileName = sprintf('cms_page_%s.zip', $this->dateTime->date('Ymd_His'));
        return $this->fileFactory->create(
            $fileName,
            [
                'type' => 'filename',
                'value' => $this->importExportContentInterface->createZipFile($pages, $fileType, $fileName),
                'rm' => true,
            ],
            DirectoryList::VAR_DIR,
            'application/zip'
        );
    }

    private function getFileType()
    {
        return $this->getRequest()->getParam('type') ?? 'json';
    }
}
