<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Ui\HistoryView;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Overdose\CMSContent\File\FileManagerInterface;
use Overdose\CMSContent\Model\BackupManager;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var BackupManager
     */
    private $backupManager;

    /**
     * @var FileManagerInterface
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
     * @param FileManagerInterface $file
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        BackupManager $backupManager,
        FileManagerInterface $file,
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
        $backupItemIdentifier = $this->request->getParam('bc_identifier');
        $backupItemName       = $this->request->getParam('item');
        $backupItemType       = $this->request->getParam('bc_type');
        $backupStoreId        = $this->request->getParam('store_id');

        $path = $this->backupManager->getBackupPath($backupItemType, $backupItemIdentifier, $backupStoreId)
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
