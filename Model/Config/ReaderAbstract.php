<?php

namespace Overdose\CMSContent\Model\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Overdose\CMSContent\Exception\InvalidXmlImportFilesException;
use Overdose\CMSContent\Model\Config\App\FileResolver;
use Overdose\CMSContent\Model\Config\Block\Reader as BlockReader;
use Psr\Log\LoggerInterface;

abstract class ReaderAbstract extends Filesystem
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_PRIMARY = 'primary';
    const FILE_NAME = '';
    const OD_CONFIG_DIR_NAME = 'od_cms';

    /**
     * @var array
     */
    protected $_idAttributes = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileResolver $fileResolver,
        ConverterInterface $converter,
        SchemaLocatorInterface $schemaLocator,
        ValidationStateInterface $validationState,
        LoggerInterface $logger,
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'primary'
    ) {
        $this->logger = $logger;

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

        $configGlobal = $this->_readFiles($fileListGlobal);
        $configPrimary = $this->_readFiles($fileListPrimary);

        if ($duplicateItems = array_intersect_key($configGlobal, $configPrimary)) {
            $listOfImportFiles = array_merge(array_keys($fileListGlobal), array_keys($fileListPrimary));
            $this->handleDuplicationError($duplicateItems, $listOfImportFiles);
        }

        return array_merge($configGlobal, $configPrimary);
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

    /**
     * @param array $duplicateItems
     * @param array $listOfImportFiles
     * @return mixed
     * @throws InvalidXmlImportFilesException
     */
    private function handleDuplicationError(array $duplicateItems, array $listOfImportFiles)
    {
        $entityName = $this->_fileName === BlockReader::FILE_NAME ? 'block' : 'page';
        $duplicates = '';
        foreach ($duplicateItems as $item) {
            $duplicates .= sprintf(
                "%s identifier \"%s\" for the store id - %s, ",
                $entityName,
                $item[ContentVersionInterface::IDENTIFIER],
                $item[ContentVersionInterface::STORE_IDS] ?: '0'
            );
        }
        $errorMessage = __(
            "CMS import failed. You have duplicate %1s. "
            . 'Please check your import files. List of duplicates - %2.',
            $entityName,
            $duplicates
        );
        $this->logger->critical(__(
            $errorMessage . "\n List of import files for the duplication check - %1",
            implode(', ', $listOfImportFiles)
        ));

        throw new InvalidXmlImportFilesException($errorMessage);
    }
}
