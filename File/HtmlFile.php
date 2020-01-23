<?php

namespace Overdose\CMSContent\File;

use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File;

class HtmlFile implements FileInterface
{
    const FILE_EXTENSION = '.html';
    /**
     * @var File
     */
    private $fileIo;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * HtmlFile constructor.
     *
     * @param File $fileIo
     * @param LoggerInterface $logger
     */
    public function __construct(
        File $fileIo,
        LoggerInterface $logger
    ) {
        $this->fileIo = $fileIo;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function writeData($path, $fileName, $content)
    {
        if (!$path || !$fileName) {
            return $this;
        }

        try {
            $this->fileIo->checkAndCreateFolder($path, 0775);
            $this->fileIo->open(array('path'=>$path));
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
}
