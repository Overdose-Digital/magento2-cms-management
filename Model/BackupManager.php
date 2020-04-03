<?php

namespace Overdose\CMSContent\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
use Overdose\CMSContent\File\FileInterface;

class BackupManager
{
    const TYPE_CMS_BLOCK = 'cms_block';
    const TYPE_CMS_PAGE = 'cms_page';

    private $directoryPathMap = [
        self::TYPE_CMS_BLOCK    => '/cms/blocks/history/',
        self::TYPE_CMS_PAGE     => '/cms/pages/history/',
    ];

    private $cmsObject = null;

    /**
     * @var FileInterface
     */
    private $file;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * BackupManager constructor.
     *
     * @param DirectoryList $directoryList
     * @param FileDriver $fileDriver
     * @param FileInterface $file
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $directoryList,
        FileDriver  $fileDriver,
        FileInterface $file,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->logger = $logger;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Creates backup file
     *
     * @param $type
     * @param $cmsObject
     * @return $this
     */
    public function createBackup($type, $cmsObject)
    {
        $this->setCmsObject($cmsObject);
        $this->file->writeData(
            $this->getBackupPath($type),
            $this->generateBackupName(),
            $this->prepareBackupContent()
        );

        return $this;
    }

    /**
     * Generates backup filename
     *
     * @return string
     */
    public function generateBackupName()
    {
        $datePart = date('Y_m_d_h_i_s', time());
        $storeIds = $this->cmsObject->getStoreId();
        if (!is_array($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }
        if (in_array(0, $storeIds)) {
            $storeIds = [0];
        }
        $storePart = '_store_' . implode('_' , $storeIds);

        return  $datePart . $storePart;
    }

    /**
     * Generates path to backup file
     *
     * @param $type
     * @return bool|string
     */
    public function getBackupPath($type, $identifier = '')
    {
        try {
            $varPath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $identifier = $identifier ?: $this->cmsObject->getIdentifier();
            $backupPath = $this->directoryPathMap[$type] . $identifier;
        } catch (\Exception $e) {
            $this->logger->critical(__('Something went wrong while retrieving filepath'));

            return false;
        }

        return $varPath . $backupPath;
    }

    /**
     * Generates content to be written to backup fie
     *
     * @return mixed
     */
    public function prepareBackupContent()
    {
        return $this->cmsObject->getOrigData('content');
    }

    /**
     * Retrieve list of backups for cms-object
     *
     * @param $type
     * @param $cmsObject
     * @return array
     * @throws FileSystemException
     */
    public function getBackupsByCmsEntity($type, $cmsObject)
    {
        if (!$cmsObject) {
            return [];
        }

        $this->setCmsObject($cmsObject);
        $result = [];
        $backupsDir = $this->getBackupPath($type);
        try {
            if ($this->fileDriver->isDirectory($backupsDir)) {
                $backups = $this->fileDriver->readDirectory($backupsDir);
                foreach ($backups as $backup) {
                    $result[] =  [
                        'name' => basename($backup),
                        'identifier' => $this->cmsObject->getIdentifier(),
                    ];
                }
            }
        } catch (FileSystemException $e) {
            $this->logger->critical(__('Something went wrong while reading backups'));
        }

        return $result;
    }

    /**
     *  Setter for $cmsObject
     *
     * @param $cmsObject
     * @return $this
     */
    public function setCmsObject($cmsObject) {
        $this->cmsObject = $cmsObject;

        return $this;
    }
}
