<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Controller\Adminhtml\Block;

use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Overdose\CMSContent\Api\ContentExportInterface;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;
use Overdose\CMSContent\Controller\Adminhtml\AbstractMassExport;

class MassExport extends AbstractMassExport implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Overdose_CMSContent::export_block';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Action\Context $context
     * @param Filter $filter
     * @param ContentExportInterface $contentExport
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param CmsEntityConverterManagerInterface $cmsEntityConverterManager
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        ContentExportInterface $contentExport,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        DateTime $dateTime,
        CmsEntityConverterManagerInterface $cmsEntityConverterManager
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->contentExport = $contentExport;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->cmsEntityConverterManager = $cmsEntityConverterManager;

        parent::__construct($context);
    }

    /**
     * Make mass export
     *
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function execute(): ResponseInterface
    {
        $blocks = $this->filter->getCollection($this->collectionFactory->create())->getItems();

        $fileName = sprintf('cms_block_%s.zip', $this->dateTime->date('Ymd_His'));

        $convertedBlocks = $this->cmsEntityConverterManager
            ->getConverter(CmsEntityConverterManagerInterface::BLOCK_ENTITY_CODE)
            ->convertToArray($blocks);

        return $this->formFile(
            $fileName,
            $convertedBlocks,
            CmsEntityConverterManagerInterface::BLOCK_ENTITY_CODE
        );
    }
}
