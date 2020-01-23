<?php

namespace Overdose\CMSContent\File;

interface FileInterface
{
    /**
     * Write prepared content to file
     *
     * @param $path string
     * @param $fileName string
     * @param $content string
     * @return mixed
     */
    public function writeData($path, $fileName, $content);

    /**
     * Read content from file
     *
     * @param $filename
     * @return mixed
     */
    public function readData($filename);
}
