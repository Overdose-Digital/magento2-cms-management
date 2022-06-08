<?php

namespace Overdose\CMSContent\Model\Config;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Config\SchemaLocatorInterface;

abstract class SchemaLocatorAbstract implements SchemaLocatorInterface
{
    protected $schemaFile = '';

    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    private $schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    private $perFileSchema;

    /**
     * SchemaLocator constructor.
     *
     * @param Reader $moduleReader
     */
    public function __construct(
        Reader $moduleReader
    ) {
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Overdose_CMSContent')
            . DIRECTORY_SEPARATOR . $this->schemaFile;
        $this->perFileSchema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Overdose_CMSContent')
            . DIRECTORY_SEPARATOR . $this->schemaFile;
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @inheritDoc
     */
    public function getPerFileSchema(): ?string
    {
        return $this->perFileSchema;
    }
}
