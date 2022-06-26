<?php

declare(strict_types=1);

namespace Overdose\CMSContent\File;

interface FileManagerInterface
{
    const FILE_EXTENSION = '.html';

    /**
     * Write prepared content to file
     *
     * @param string $path
     * @param string $fileName
     * @param string $content
     * @param string $fileExtension
     *
     * @return FileManagerInterface
     */
    public function writeData(
        string $path,
        string $fileName,
        string $content,
        string $fileExtension = self::FILE_EXTENSION
    ): FileManagerInterface;

    /**
     * Read content from file
     *
     * @param $filename
     *
     * @return mixed
     */
    public function readData($filename);
}
