<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Ui\DataProvider;

use Laminas\Json\Json;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Overdose\CMSContent\File\FileManagerInterface;
use Overdose\CMSContent\Model\BackupManager;

class HistoryView extends AbstractDataProvider
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
     * @var Json
     */
    private $jsonFormatter;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param BackupManager $backupManager
     * @param Json $jsonFormatter
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
        Json $jsonFormatter,
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
        $this->jsonFormatter = $jsonFormatter;
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
            $parsedArray = $this->jsonFormatter->decode($this->getBackupContent(), true);
            $data[$backupItemIdentifier] = [
                'identifier' => $parsedArray['identifier'] ?? '',
                'title' => $parsedArray['title'] ?? '',
                'content' => $parsedArray['content'] ?? ''
            ];
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
        $itemIdentifier = $this->request->getParam('bc_identifier');
        $itemName = $this->request->getParam('item');
        $itemType = $this->request->getParam('bc_type');
        $storeId  = $this->request->getParam('store_id');

        if (!is_null($storeId)) {
            $path = $this->backupManager->getBackupPathByStoreId($itemType, $itemIdentifier, (int)$storeId)
                . DIRECTORY_SEPARATOR . $itemName;
        } else {
            $path = $this->backupManager->getBackupPath($itemType, $itemIdentifier)
                . DIRECTORY_SEPARATOR . $itemName;
        }

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
