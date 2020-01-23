<?php

namespace Overdose\CMSContent\Model\Config;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Config\Reader\Filesystem;

abstract class ReaderAbstract extends Filesystem
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_PRIMARY = 'primary';

    protected $_idAttributes = [];

    /**
     * Reader constructor.
     *
     * @param FileResolverInterface $fileResolver
     * @param ConverterInterface $converter
     * @param SchemaLocatorInterface $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        ConverterInterface $converter,
        SchemaLocatorInterface $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = null,
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'primary'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function read($scope = null)
    {
        $fileListGlobal = $this->_fileResolver->get($this->_fileName, self::SCOPE_GLOBAL);
        $fileListGlobal = count($fileListGlobal) ? $fileListGlobal->toArray() : [];
        $fileListPrimary = $this->_fileResolver->get($this->_fileName, self::SCOPE_PRIMARY);
        $fileListPrimary = count($fileListPrimary) ? $fileListPrimary->toArray() : [];

        return $this->_readFiles(array_merge($fileListGlobal, $fileListPrimary));
    }
}
