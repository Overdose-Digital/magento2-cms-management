<?php

namespace Overdose\CMSContent\Model;

use Overdose\CMSContent\Api\Data\ContentVersionInterfaceFactory;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;
use Magento\Framework\Api\DataObjectHelper;

class ContentVersion extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;
    protected $content_versionDataFactory;
    protected $_eventPrefix = 'od_cmscontent_version';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ContentVersionInterfaceFactory $content_versionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Overdose\CMSContent\Model\ResourceModel\ContentVersion $resource
     * @param \Overdose\CMSContent\Model\ResourceModel\ContentVersion\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ContentVersionInterfaceFactory $content_versionDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Overdose\CMSContent\Model\ResourceModel\ContentVersion $resource,
        \Overdose\CMSContent\Model\ResourceModel\ContentVersion\Collection $resourceCollection,
        array $data = []
    ) {
        $this->content_versionDataFactory = $content_versionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve content_version model with content_version data
     * @return ContentVersionInterface
     */
    public function getDataModel()
    {
        $content_versionData = $this->getData();
        $content_versionDataObject = $this->content_versionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $content_versionDataObject,
            $content_versionData,
            ContentVersionInterface::class
        );
        
        return $content_versionDataObject;
    }
}
