<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Observer;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Overdose\CMSContent\Api\ContentVersionManagementInterface;
use Overdose\CMSContent\Api\Data\ContentVersionInterface;

class DeleteContentVersion implements ObserverInterface
{
    /**
     * Events map to process
     *
     * @var array
     */
    private $eventsTypeMap = [
        'cms_page_delete_commit_after' => ContentVersionInterface::TYPE_PAGE,
        'cms_block_delete_commit_after' => ContentVersionInterface::TYPE_BLOCK,
    ];

    /**
     * @var ContentVersionManagementInterface
     */
    private $contentVersionManagement;

    /**
     * @param ContentVersionManagementInterface $contentVersionManagement
     */
    public function __construct(
        ContentVersionManagementInterface $contentVersionManagement
    ) {
        $this->contentVersionManagement = $contentVersionManagement;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $eventName = $observer->getEvent()->getName();
        if (!array_key_exists($eventName, $this->eventsTypeMap)) {
            return;
        }
        /** @var PageInterface|BlockInterface $cmsModel */
        $cmsModel = $observer->getEvent()->getData('data_object');
        $this->contentVersionManagement->deleteContentVersion(
            $cmsModel->getIdentifier(),
            $this->eventsTypeMap[$eventName],
            $cmsModel->getStores()
        );
    }
}
