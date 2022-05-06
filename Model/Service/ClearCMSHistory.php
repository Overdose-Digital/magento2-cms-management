<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Service;

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
    private DateTime $dateTime;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var File
     */
    private File $fileManger;

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
     * Get module version
     *
     * @return int
     */
    public function execute(): int
    {
        $count = 0;
        switch ($this->config->getMethodType()) {
            case Config::PERIOD:
                $count = $this->clearByPeriod();
                break;
            case Config::OLDER_THAN:
                $count = $this->clearOlderThan();
                break;
        }
        return $count;
    }

    /**
     * Delete files by periods
     *
     * @return int
     */
    private function clearByPeriod(): int
    {
        $filesByPeriods = $this->formFilesByPeriods();
        if (count($filesByPeriods)) {
            return $this->deleteFiles($filesByPeriods, Config::PERIOD);
        }
        return 0;
    }

    /**
     * Delete files older than some period
     *
     * @return int
     */
    private function clearOlderThan(): int
    {
        $filesOlderThan = $this->findFilesOlderThan();
        if (count($filesOlderThan)) {
            return $this->deleteFiles($filesOlderThan, Config::OLDER_THAN);
        }
        return 0;
    }

    /**
     * Form list of files by periods
     *
     * @return array
     */
    private function formFilesByPeriods(): array
    {
        $now = $this->dateTime->gmtTimestamp();

        $filesByPeriod = $filesToDelete = [];
        foreach ($this->getFilesList() as $file) {
            if ($now - filemtime($file) >= Config::WEEK && $now - filemtime($file) < Config::MONTH) {
                $filesByPeriod[Config::WEEK][] = $file;
            }

            if ($now - filemtime($file) >= Config::MONTH && $now - filemtime($file) < Config::YEAR) {
                $filesByPeriod[Config::MONTH][] = $file;
            }

            if ($now - filemtime($file) >= Config::YEAR) {
                $filesByPeriod[Config::YEAR][] = $file;
            }
        }

        foreach ($filesByPeriod as $files) {
            usort($files, static fn ($a, $b) => filemtime($b) - filemtime($a));
            array_shift($files);
            array_push($filesToDelete, ...$files);
        }
        return $filesToDelete;
    }

    /**
     * Fina files which are older than
     *
     * @return array
     */
    private function findFilesOlderThan(): array
    {
        $now = $this->dateTime->gmtTimestamp();
        $period = $this->config->getPeriodType() * $this->config->getPeriodNumber();

        $files = [];
        foreach ($this->getFilesList() as $file) {
            if ($now - filemtime($file) >= $period) {
                $files[] = $file;
            }
        }
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
     * @return array
     */
    private function getFilesList(): array
    {
        $result = [];
        try {
            $result = $this->fileManger->readDirectoryRecursively($this->config->getBackupsDir());
            $result = array_filter(
                $result,
                fn($file) => (!$this->fileManger->isDirectory($file) && strpos($file, Config::HISTORY_DIR))
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }
}
