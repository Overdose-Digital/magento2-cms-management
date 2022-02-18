<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Overdose\CMSContent\Model\Config\Cron\CronConfig;
use Psr\Log\LoggerInterface;

class DeleteBackups
{
    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var CronConfig
     */
    private CronConfig $cronConfig;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param DirectoryList $directoryList
     * @param DateTime $dateTime
     * @param CronConfig $cronConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $directoryList,
        DateTime $dateTime,
        CronConfig $cronConfig,
        LoggerInterface $logger
    )
    {
        $this->directoryList = $directoryList;
        $this->dateTime = $dateTime;
        $this->cronConfig = $cronConfig;
        $this->logger = $logger;
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    public function execute()
    {
        if ($this->cronConfig->isCronEnabled()) {

            $now = $this->dateTime->gmtTimestamp();
            $varPath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $cmsPath = $varPath . DS . CronConfig::CMS_DIR;
            $directoryIterator = new \RecursiveDirectoryIterator($cmsPath, \FilesystemIterator::SKIP_DOTS);

            if ($this->cronConfig->getMethodType() === CronConfig::PERIOD) {
                $filesByPeriods = $this->findAndSortFilesByPeriods($directoryIterator, $now);

                if (count($filesByPeriods)) {
                    $deletedFiles = [];

                    foreach ($filesByPeriods as $filesByPeriod) {
                        $deletedFiles[] = $this->deleteFilesByPeriod($filesByPeriod['files'], $filesByPeriod['period']);
                    }

                    if($this->cronConfig->isLogsEnabled()) {
                        $this->logger->debug(
                            'CMS Content. Delete files by cron:',
                            ['method' => CronConfig::PERIOD, 'files' => $deletedFiles]
                        );
                    }
                }
            } else if ($this->cronConfig->getMethodType() === CronConfig::OLDER_THAN) {
                $filesOlderThan = $this->findFilesOlderThan($directoryIterator, $now);

                if (count($filesOlderThan)) {
                    $this->deleteFilesOlderThan($filesOlderThan);

                    if($this->cronConfig->isLogsEnabled()) {
                        $this->logger->debug(
                            'CMS Content. Delete files by cron:',
                            ['method' => CronConfig::OLDER_THAN, 'files' => $filesOlderThan]
                        );
                    }
                }
            }

        }
    }

    /**
     * @param $directoryIterator
     * @param $now
     * @return array
     */
    private function findAndSortFilesByPeriods($directoryIterator, $now): array
    {
        $files = $filesByWeeks = $filesByMonths = $filesByYears = [];

        foreach (new \RecursiveIteratorIterator($directoryIterator) as $file) {
            $filePath = $file->getPathname();
            if (strpos($filePath, CronConfig::HISTORY_DIR)) {

                if ($now - filemtime($filePath) >= CronConfig::WEEK && $now - filemtime($filePath) < CronConfig::MONTH) {
                    $filesByWeeks[] = $filePath;
                }

                if ($now - filemtime($filePath) >= CronConfig::MONTH && $now - filemtime($filePath) < CronConfig::YEAR) {
                    $filesByMonths[] = $filePath;
                }

                if ($now - filemtime($filePath) >= CronConfig::YEAR) {
                    $filesByYears[] = $filePath;
                }
            }
        }

        if (count($filesByWeeks)) {
            $files['week']['files'] = $filesByWeeks;
            $files['week']['period'] = CronConfig::WEEK;
        }

        if (count($filesByMonths)) {
            $files['month']['files'] = $filesByMonths;
            $files['month']['period'] = CronConfig::MONTH;
        }

        if (count($filesByYears)) {
            $files['year']['files'] = $filesByYears;
            $files['year']['period'] = CronConfig::YEAR;
        }

        return $files;
    }

    /**
     * @param $files
     * @param $period
     * @return array
     */
    private function deleteFilesByPeriod($files, $period): array
    {
        usort($files, function ($a, $b) { return filemtime($b) - filemtime($a); });

        $previousFile = null;
        $deletedFiles = [];

        foreach ($files as $file) {
            if (!$previousFile) $previousFile = $file;

            if ($previousFile !== $file) {
                if (filemtime($previousFile) - $period >= filemtime($file)) {
                    $previousFile = $file;
                } else {
                    $deletedFiles[] = $file;
                    unlink($file);
                }
            }
        }

        return $deletedFiles;
    }

    /**
     * @param $directoryIterator
     * @param $now
     * @return array
     */
    private function findFilesOlderThan($directoryIterator, $now): array
    {
        $period = $this->cronConfig->getPeriodType() * $this->cronConfig->getPeriodNumber();
        $files = [];

        foreach (new \RecursiveIteratorIterator($directoryIterator) as $file) {
            $filePath = $file->getPathname();
            if (strpos($filePath, CronConfig::HISTORY_DIR)) {
                if ($now - filemtime($filePath) >= $period) {
                    $files[] = $filePath;
                }
            }
        }

        return $files;
    }

    /**
     * @param $files
     * @return void
     */
    private function deleteFilesOlderThan($files)
    {
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
