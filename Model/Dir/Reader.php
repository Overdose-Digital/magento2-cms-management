<?php
declare(strict_types=1);

namespace Overdose\CMSContent\Model\Dir;

use Magento\Framework\Module\Dir;
use Overdose\CMSContent\Model\Config\Block\Reader as BlockReader;
use Overdose\CMSContent\Model\Config\Page\Reader as PageReader;
use Overdose\CMSContent\Model\Config\ReaderAbstract;

class Reader extends \Magento\Framework\Module\Dir\Reader
{
    /**
     * @inheridoc
     */
    public function getConfigurationFiles($filename)
    {
        return $this->getFilesIterator($filename, Dir::MODULE_ETC_DIR);
    }

    /**
     * @inheridoc
     */
    private function getFilesIterator($filename, $subDir = '')
    {
        if (!isset($this->fileIterators[$subDir][$filename])) {
            $this->fileIterators[$subDir][$filename] = $this->fileIteratorFactory->create(
                $this->getFiles($filename, $subDir)
            );
        }
        return $this->fileIterators[$subDir][$filename];
    }

    /**
     * @inheridoc
     */
    private function getFiles($filename, $subDir = '')
    {
        $result = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            try {
                $moduleSubDir = $this->getModuleDir($subDir, $moduleName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            $file = $moduleSubDir . '/' . $filename;
            $directoryRead = $this->readFactory->create($moduleSubDir);
            $path = $directoryRead->getRelativePath($file);
            if ($directoryRead->isExist($path)) {
                $result[] = $file;
            }
            // === Overdose - the override part BEGIN ===
            if ($filename === BlockReader::FILE_NAME || $filename === PageReader::FILE_NAME) {
                $pattern = substr($filename, 0, strlen($filename) - 4);
                $dir = $moduleSubDir . '/' . ReaderAbstract::OD_CONFIG_DIR_NAME;
                $directoryRead = $this->readFactory->create($dir);
                $path = $directoryRead->getRelativePath($dir);
                if ($directoryRead->isExist($path)) {
                    $odFiles = array_filter($directoryRead->read($path), function ($file) use ($pattern) {
                        return strpos($file, $pattern) === 0;
                    });
                    array_walk($odFiles, function (&$file) use ($dir) {
                        $file = $dir . '/' . $file;
                    });
                    $result = array_merge($odFiles, $result);
                }
            }
            // === Overdose - the override part END ===
        }
        return $result;
    }
}
