<?php

namespace Overdose\CMSContent\Controller\Adminhtml\Page;

use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Overdose\CMSContent\Api\ContentExportInterface;

class MassExport extends Action
{
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ContentExportInterface
     */
    private $contentExport;
    /**
     * @var FileFactory
     */
    private $fileFactory;
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        ContentExportInterface $contentExport,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        DateTime $dateTime
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->contentExport = $contentExport;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;

        parent::__construct($context);
    }

    /**
     * @inheridoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Overdose_CMSContent::export_page');
    }

    /**
     * @inheridoc
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $pages = [];
        foreach ($collection as $page) {
            $pages[] = $page;
        }

        $fileName = sprintf('cms_page_%s.zip', $this->dateTime->date('Ymd_His'));
        $fileType = $this->getRequest()->getParam('type') ?? 'json';
        $isSplit = $this->getRequest()->getParam('split') ?? false;

        return $this->fileFactory->create(
            $fileName,
            [
                'type' => 'filename',
                'value' => $this->contentExport->createZipFile($pages, $fileType, $fileName, $isSplit),
                'rm' => true,
            ],
            DirectoryList::VAR_DIR,
            'application/zip'
        );
    }
}
