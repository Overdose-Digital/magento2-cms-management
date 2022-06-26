<?php

namespace Overdose\CMSContent\Model\Config\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Filesystem;
use Overdose\CMSContent\Model\Dir\Reader;

class FileResolver implements FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var Reader
     */
    protected $moduleReader;

    /**
     * File iterator factory
     *
     * @var FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Reader $moduleReader
     * @param Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        Reader $moduleReader,
        Filesystem $filesystem,
        FileIteratorFactory $iteratorFactory
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'primary':
                $directory = $this->filesystem->getDirectoryRead(DirectoryList::CONFIG);
                $absolutePaths = [];
                foreach ($directory->search('{' . $filename . ',*/' . $filename . '}') as $path) {
                    $absolutePaths[] = $directory->getAbsolutePath($path);
                }
                $iterator = $this->iteratorFactory->create($absolutePaths);
                break;
            case 'global':
                $iterator = $this->moduleReader->getConfigurationFiles($filename);
                break;
            default:
                $iterator = $this->moduleReader->getConfigurationFiles($scope . '/' . $filename);
                break;
        }
        return $iterator;
    }
}
