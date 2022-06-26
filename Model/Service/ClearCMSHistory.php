<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Service;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Overdose\CMSContent\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Class ClearCMSHistory
 */
class ClearCMSHistory
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $fileManger;

    /**
     * @param DateTime $dateTime
     * @param Config $config
     * @param LoggerInterface $logger
     * @param File $fileManger
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        LoggerInterface $logger,
        File $fileManger
    ) {
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->logger = $logger;
        $this->fileManger = $fileManger;
    }

    /**
     * Delete old backups
     *
     * @param string $type
     *
     * @return int
     * @throws FileSystemException
     */
    public function execute(string $type): int
    {
        $count = 0;
        foreach ($this->getItemsByType($type) as $folder) {
            $items = $this->getFilesList($folder);
            if (!count($items)) {
                continue;
            }

            if ($this->fileManger->isFile($items[0])) {
                $count = $this->clear($folder, $count);
            } else {
                foreach ($items as $item) {
                    $count = $this->clear($item, $count);
                }
            }
        }
        return $count;
    }

    /**
     * Clear folders
     *
     * @param string $folder
     * @param int $count
     *
     * @return int
     */
    private function clear(string $folder, int $count): int
    {
        switch ($this->config->getMethodType()) {
            case Config::PERIOD:
                $count += $this->clearByPeriod($folder, $count);
                break;
            case Config::OLDER_THAN:
                $count += $this->clearOlderThan($folder, $count);
                break;
        }
        return $count;
    }

    /**
     * Delete files by periods
     *
     * @param string $folder
     * @param int $count
     *
     * @return int
     */
    private function clearByPeriod(string $folder, int $count): int
    {
        $filesByPeriods = $this->formFilesByPeriods($folder);
        if (count($filesByPeriods)) {
            $count = $this->deleteFiles($filesByPeriods, Config::PERIOD);
        }
        return $count;
    }

    /**
     * Delete files older than some period
     *
     * @param string $folder
     * @param int $count
     *
     * @return int
     */
    private function clearOlderThan(string $folder, int $count): int
    {
        $filesOlderThan = $this->findFilesOlderThan($folder);
        if (count($filesOlderThan)) {
            $count = $this->deleteFiles($filesOlderThan, Config::OLDER_THAN);
        }
        return $count;
    }

    /**
     * Form list of files by periods
     *
     * @param string $item
     *
     * @return array
     */
    private function formFilesByPeriods(string $item): array
    {
        $now = $this->dateTime->gmtTimestamp();
        $filesByPeriod = $filesToDelete = [];

        foreach ($this->getFilesList($item) as $file) {
            if ($now - filemtime($file) >= Config::WEEK && $now - filemtime($file) < Config::MONTH) {
                $week = date('W', filemtime($file));
                $filesByPeriod[$week][] = $file;
            }

            if ($now - filemtime($file) >= Config::MONTH && $now - filemtime($file) < Config::YEAR) {
                $month = date('Y-m', filemtime($file));
                $filesByPeriod[$month][] = $file;
            }

            if ($now - filemtime($file) >= Config::YEAR) {
                $year = date('Y', filemtime($file));
                $filesByPeriod[$year][] = $file;
            }
        }

        foreach ($filesByPeriod as $files) {
            $files = $this->leftNewestFile($files);
            array_push($filesToDelete, ...$files);
        }
        return $filesToDelete;
    }

    /**
     * Fina files which are older than
     *
     * @param string $item
     *
     * @return array
     */
    private function findFilesOlderThan(string $item): array
    {
        $now = $this->dateTime->gmtTimestamp();
        $period = $this->config->getPeriodType() * $this->config->getPeriodNumber();

        $files = [];
        foreach ($this->getFilesList($item) as $file) {
            if ($now - filemtime($file) >= $period) {
                $files[] = $file;
            }
        }
        return $this->leftNewestFile($files);
    }

    /**
     * Left newest file from array
     *
     * @param array $files
     *
     * @return array
     */
    private function leftNewestFile(array $files): array
    {
        usort($files, function ($a, $b) {
            return filemtime($a) < filemtime($b);
        });
        array_shift($files);

        return $files;
    }

    /**
     * Delete files
     *
     * @param array $files
     * @param string $method
     *
     * @return int
     */
    private function deleteFiles(array $files, string $method): int
    {
        $deletedFiles = [];
        foreach ($files as $file) {
            try {
                $this->fileManger->deleteFile($file);
                $deletedFiles[] = $file;
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        if ($this->config->isLogsEnabled()) {
            $this->logger->debug(
                'CMS Content. Delete files by cron:',
                [
                    'method' => $method,
                    'files' => $deletedFiles
                ]
            );
        }
        return count($deletedFiles);
    }

    /**
     * Get files list
     *
     * @param string $itemDir
     *
     * @return array
     */
    private function getFilesList(string $itemDir): array
    {
        $result = [];
        try {
            $result = $this->fileManger->readDirectory($itemDir);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }

    /**
     * Get list of items by type ( blocks, pages )
     *
     * @param string $type
     *
     * @return array
     */
    private function getItemsByType(string $type): array
    {
        $result = [];
        try {
            $result = $this->fileManger->readDirectory(
                $this->config->getBackupsDir()
                . DIRECTORY_SEPARATOR
                . $type
                . DIRECTORY_SEPARATOR
                . Config::HISTORY_DIR
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }
}
