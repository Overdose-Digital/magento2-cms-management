<?php

namespace Overdose\CMSContent\Model\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Model\Config\App\FileResolver;

abstract class ReaderAbstract extends Filesystem
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_PRIMARY = 'primary';
    const FILE_NAME = 'cms_od_data.xml';
    const OD_CONFIG_DIR_NAME = 'od_cms';

    protected $_idAttributes = [];

    /**
     * Reader constructor.
     *
     * @param FileResolver $fileResolver
     * @param ConverterInterface $converter
     * @param SchemaLocatorInterface $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolver $fileResolver,
        ConverterInterface $converter,
        SchemaLocatorInterface $schemaLocator,
        ValidationStateInterface $validationState,
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'primary'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            static::FILE_NAME,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    /**
     * Load configuration for primary and global scopes
     *
     * @param null $scope
     * @return array
     * @throws LocalizedException
     */
    public function read($scope = null)
    {
        $fileListGlobal = $this->_fileResolver->get($this->_fileName, self::SCOPE_GLOBAL);
        $fileListGlobal = count($fileListGlobal) ? $fileListGlobal->toArray() : [];
        $fileListPrimary = $this->_fileResolver->get($this->_fileName, self::SCOPE_PRIMARY);
        $fileListPrimary = count($fileListPrimary) ? $fileListPrimary->toArray() : [];

        return $this->_readFiles(array_merge($fileListGlobal, $fileListPrimary));
    }

    /**
     * @param string $file
     * @return array
     * @throws LocalizedException
     */
    public function readFromFile(string $file)
    {
        return $this->_readFiles([$file => file_get_contents($file)]);
    }
}
