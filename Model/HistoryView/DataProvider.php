<?php

namespace Overdose\CMSContent\Model\HistoryView;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Overdose\CMSContent\Model\BackupManager;
use Overdose\CMSContent\File\FileInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var BackupManager
     */
    private $backupManager;

    /**
     * @var FileInterface
     */
    private $file;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param BackupManager $backupManager
     * @param FileInterface $file
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        BackupManager $backupManager,
        FileInterface $file,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->backupManager = $backupManager;
        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        $data = [];
        if ($backupItemIdentifier = $this->request->getParam('bc_identifier')) {
            $data[$backupItemIdentifier]['content'] = $this->getBackupContent();
        }

        return $data;
    }

    /**
     * Retrieve content from backup
     *
     * @return mixed
     */
    public function getBackupContent()
    {
        $backupItemIdentifier   = $this->request->getParam('bc_identifier');
        $backupItemName         = $this->request->getParam('item');
        $backupItemType         = $this->request->getParam('bc_type');

        $path = $this->backupManager->getBackupPath($backupItemType, $backupItemIdentifier)
            . DIRECTORY_SEPARATOR . $backupItemName;

        return $this->file->readData($path);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(Filter $filter)
    {
        return $this;
    }
}
