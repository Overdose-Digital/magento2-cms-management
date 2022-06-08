<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\FileSystemException;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Overdose\CMSContent\File\FileManagerInterface;

class BackupManager
{
    const TYPE_CMS_BLOCK = 'cms_block';
    const TYPE_CMS_PAGE = 'cms_page';

    private $cmsObject = null;

    /**
     * @var FileManagerInterface
     */
    private $file;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var Config
     */
    private $config;

    /**
     * BackupManager constructor.
     *
     * @param File $fileDriver
     * @param FileManagerInterface $file
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        File $fileDriver,
        FileManagerInterface $file,
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
     *
     * @return BackupManager
     */
    public function createBackup($type, $cmsObject): BackupManager
    {
        $this->setCmsObject($cmsObject);
        foreach ($this->prepareStoreIds() as $storeId) {
            $this->file->writeData(
                $this->getBackupPathByStoreId($type, $this->cmsObject->getIdentifier(), (int)$storeId),
                $this->generateBackupName((int)$storeId),
                $this->prepareBackupContent()
            );
        }
        return $this;
    }

    /**
     * Generates backup filename in format: Y_m_d_h_i_s_store_id
     *
     * @param int $storeId
     *
     * @return string
     */
    public function generateBackupName(int $storeId): string
    {
        return sprintf('%s_store_%s', date('Y_m_d_h_i_s', time()), $storeId);
    }

    /**
     * Generates path to backup file
     *
     * @param string $type
     * @param string $identifier
     * @param int $storeId
     *
     * @return null|string
     */
    public function getBackupPathByStoreId(
        string $type,
        string $identifier = '',
        int $storeId = Store::DEFAULT_STORE_ID
    ): ?string {
        try {
            $identifier = $identifier ?: $this->cmsObject->getIdentifier();

            return $this->getBaseBackupPath($type) . 'history'
                . DIRECTORY_SEPARATOR . $storeId . DIRECTORY_SEPARATOR . $identifier . DIRECTORY_SEPARATOR;
        } catch (\Exception $e) {
            $this->logger->critical(__('Something went wrong while retrieving filepath'));

            return null;
        }
    }

    /**
     * Get backup path
     * This function is for backward compatibility with previous code
     *
     * @param string $type
     * @param string $identifier
     *
     * @return string|null
     */
    public function getBackupPath(string $type, string $identifier = ''): ?string
    {
        try {
            $identifier = $identifier ?: $this->cmsObject->getIdentifier();

            return $this->getBaseBackupPath($type) . 'history'
                . DIRECTORY_SEPARATOR . $identifier . DIRECTORY_SEPARATOR;
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
     *
     * @return array
     * @throws FileSystemException
     */
    public function getBackupsByCmsEntity($type, $cmsObject): array
    {
        if (!$cmsObject) {
            return [];
        }

        $this->setCmsObject($cmsObject);
        $result = [];

        foreach ($cmsObject->getStores() as $storeId) {
            $backupsDir = $this->getBackupPathByStoreId($type, $cmsObject->getIdentifier(), (int)$storeId);
            $result = $this->getItemsByStore($result, $backupsDir, (int)$storeId);
        }

        $backupsDir = $this->getBackupPath($type, $cmsObject->getIdentifier());
        return $this->getItemsByStore($result, $backupsDir, null);
    }

    /**
     * Setter for $cmsObject
     *
     * @param $cmsObject
     *
     * @return $this
     */
    public function setCmsObject($cmsObject): BackupManager
    {
        $this->cmsObject = $cmsObject;

        return $this;
    }

    /**
     * Prepare store ids array
     *
     * @return array
     */
    public function prepareStoreIds(): array
    {
        $storeIds = $this->cmsObject->getStores();
        if (!count($storeIds) || in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds = [Store::DEFAULT_STORE_ID];
        }
        return $storeIds;
    }

    /**
     * Get items by store
     *
     * @param array $result
     * @param string $backupsDir
     * @param null|int $storeId
     *
     * @return array
     */
    private function getItemsByStore(array $result, string $backupsDir, ?int $storeId): array
    {
        try {
            if (!$this->fileDriver->isDirectory($backupsDir)) {
                return $result;
            }
            $backups = $this->fileDriver->readDirectory($backupsDir);
            foreach ($backups as $backup) {
                $result[] = [
                    'name'       => basename($backup),
                    'label'      => (!is_null($storeId))
                        ? 'store_' . $storeId . '/' . basename($backup) : basename($backup),
                    'identifier' => $this->cmsObject->getIdentifier(),
                    'store_id'   => $storeId
                ];
            }
        } catch (FileSystemException $e) {
            $this->logger->critical(__('Something went wrong while reading backups'));
        }
        return $result;
    }

    /**
     * Get base backup path
     *
     * @param string $type
     *
     * @return string
     */
    private function getBaseBackupPath(string $type): string
    {
        $cmsDir = $this->config->getBackupsDir() . DIRECTORY_SEPARATOR;
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
        return $backupPath;
    }
}
