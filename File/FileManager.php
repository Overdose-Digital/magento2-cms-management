<?php

declare(strict_types=1);

namespace Overdose\CMSContent\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem;

class FileManager implements FileManagerInterface
{
    /**
     * @var File
     */
    private $fileIo;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FileManager constructor.
     *
     * @param File $fileIo
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        File $fileIo,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->fileIo = $fileIo;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function writeData(
        string $path,
        string $fileName,
        string $content,
        string $fileExtension = self::FILE_EXTENSION
    ): FileManagerInterface {
        if (!$path || !$fileName) {
            return $this;
        }

        try {
            $path = $this->getFolder($path);
            $this->fileIo->open(['path' => $path]);
            $this->fileIo->write($fileName . self::FILE_EXTENSION, $content, 0666);
        } catch (\Exception $e) {
            $this->logger->critical(__('Something went wrong while saving file'));
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readData($filename)
    {
        return $this->fileIo->read($filename);
    }

    /**
     * Get folder
     *
     * @param string $path
     *
     * @return string;
     */
    public function getFolder(string $path): string
    {
        try {
            $this->fileIo->checkAndCreateFolder($path, 0775);

            return $path;
        } catch (\Exception $e) {
            $this->logger->critical(__('Something went wrong while saving file'));

            return '';
        }
    }

    /**
     * Get media file path
     *
     * @param string $mediaFile
     * @param bool $write = false
     *
     * @return string
     * @throws FileSystemException
     */
    public function getMediaPath(string $mediaFile, bool $write = false): ?string
    {
        if ($write) {
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        } else {
            $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }
        return $mediaDir->getAbsolutePath($mediaFile);
    }
}
