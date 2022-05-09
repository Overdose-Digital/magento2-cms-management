<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Exception\FileSystemException;
use Overdose\CMSContent\Model\Config;
use Psr\Log\LoggerInterface;
use Overdose\CMSContent\File\FileInterface;

class BackupManager
{
    const TYPE_CMS_BLOCK = 'cms_block';
    const TYPE_CMS_PAGE = 'cms_page';

    private $cmsObject = null;

    /**
     * @var FileInterface
     */
    private $file;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var Config
     */
    private $config;

    /**
     * BackupManager constructor.
     *
     * @param FileDriver $fileDriver
     * @param FileInterface $file
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileDriver  $fileDriver,
        FileInterface $file,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
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
        $storePart = '_store_' . implode('_' , $this->prepareStoreIds());

        return  $datePart . $storePart;
    }

    /**
     * Generates path to backup file
     *
     * @param string $type
     * @param string $identifier
     *
     * @return null|string
     */
    public function getBackupPath(string $type, string $identifier = ''): ?string
    {
        try {
            $cmsDir = $this->config->getBackupsDir() . DIRECTORY_SEPARATOR;
            $identifier = $identifier ?: $this->cmsObject->getIdentifier();

            switch ($type) {
                case self::TYPE_CMS_BLOCK:
                    $backupPath = $cmsDir . Config::TYPE_BLOCK . DIRECTORY_SEPARATOR;
                    break;
                case self::TYPE_CMS_PAGE:
                    $backupPath = $cmsDir . Config::TYPE_PAGE . DIRECTORY_SEPARATOR;
                    break;
                default:
                    $backupPath = $cmsDir;
                    break;
            }

            return $backupPath . 'history' . DIRECTORY_SEPARATOR . $identifier;
        } catch (\Exception $e) {
            $this->logger->critical(__('Something went wrong while retrieving filepath'));

            return null;
        }
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
     *
     * @return $this
     */
    public function setCmsObject($cmsObject)
    {
        $this->cmsObject = $cmsObject;

        return $this;
    }

    /**
     *  Prepare store ids array
     *
     * @param mixed $storeIds
     * @return array
     */
    public function prepareStoreIds()
    {
        $storeIds = $this->cmsObject->getStoreId();
        //Fix for import wth  Overdose_CMSContent
        if (null === $storeIds) {
            $storeIds = $this->cmsObject->getStores();
        }

        $storeIds = (array)$storeIds;
        if (empty($storeIds) || in_array(0, $storeIds)) {
            $storeIds = [0];
        }

        return $storeIds;
    }
}
