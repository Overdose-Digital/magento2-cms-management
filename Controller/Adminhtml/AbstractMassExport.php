<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Controller\Adminhtml;

use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Filesystem\DirectoryList;
use Overdose\CMSContent\Api\ContentExportInterface;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;

abstract class AbstractMassExport extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var ContentExportInterface
     */
    protected $contentExport;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CmsEntityConverterManagerInterface
     */
    protected $cmsEntityConverterManager;

    /**
     * Form file
     *
     * @param string $fileName
     * @param array $convertedBlocks
     * @param string $type
     *
     * @return ResponseInterface
     */
    protected function formFile(string $fileName, array $convertedBlocks, string $type): ResponseInterface
    {
        return $this->fileFactory->create(
            $fileName,
            [
                'type'  => 'filename',
                'value' => $this->returnZipFile($convertedBlocks, $fileName, $type),
                'rm'    => true,
            ],
            DirectoryList::VAR_DIR,
            'application/zip'
        );
    }

    /**
     * Return zip file
     *
     * @param array $convertedBlocks
     * @param string $fileName
     * @param string $type
     *
     * @return string
     */
    protected function returnZipFile(array $convertedBlocks, string $fileName, string $type): string
    {
        $fileType = $this->getRequest()->getParam('type', 'json');
        $isSplit  = $this->getRequest()->getParam('split', false);

        return $this->contentExport->createZipFile(
            $convertedBlocks,
            $type,
            $fileType,
            $fileName,
            $isSplit
        );
    }
}
