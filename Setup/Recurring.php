<?php

namespace Overdose\CMSContent\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @var ContentVersionManagementInterface
     */
    private $contentVersionManagement;

    public function __construct(ContentVersionManagementInterface $contentVersionManagement)
    {
        $this->contentVersionManagement = $contentVersionManagement;
    }

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->contentVersionManagement->processAll();
    }
}
