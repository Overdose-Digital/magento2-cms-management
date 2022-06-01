<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Block\Adminhtml\Cms;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Backend\Model\UrlInterface;
use Overdose\CMSContent\Model\BackupManager;

class CmsAbstract extends Template
{
    protected $urlParamId = 'id';
    protected $bcType = null;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var BackupManager
     */
    private $backupManager;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * CmsAbstract constructor.
     *
     * @param Template\Context $context
     * @param BlockRepositoryInterface $blockRepository
     * @param PageRepositoryInterface $pageRepository
     * @param BackupManager $backupManager
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        BlockRepositoryInterface $blockRepository,
        PageRepositoryInterface $pageRepository,
        BackupManager $backupManager,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageRepository = $pageRepository;
        $this->backupManager = $backupManager;
        $this->blockRepository = $blockRepository;
        $this->backendUrl = $backendUrl;
    }

    /**
     * Prepare backups list
     *
     * @return array
     * @throws LocalizedException
     */
    public function getBackups(): array
    {
        $id = $this->getRequest()->getParam($this->urlParamId);
        $cmsObject = $this->getCmsObject($id);

        return $this->backupManager->getBackupsByCmsEntity($this->bcType, $cmsObject);
    }

    /**
     * Retrieve url for viewing of backup content
     *
     * @param $backup
     * @return string
     */
    public function getBackupUrl($backup)
    {
        return $this->backendUrl->getUrl(
            'cmscontent/history/view',
            [
                'bc_type' => $this->bcType,
                'bc_identifier' => $backup['identifier'],
                'item' => $backup['name'],
            ]
        );
    }

    /**
     * Retrieve cms block or cms page by identifier
     *
     * @param $id
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface|null
     */
    public function getCmsObject($id)
    {
        try {
            if ($this->bcType == BackupManager::TYPE_CMS_BLOCK) {
                return $this->blockRepository->getById($id);
            } else if ($this->bcType == BackupManager::TYPE_CMS_PAGE) {
                return $this->pageRepository->getById($id);
            }

            return null;
        } catch (LocalizedException $e) {
            $this->_logger->critical(__('Something went wrong while getting cms block or page %1', $id));
        } catch (\Exception $e) {
            $this->_logger->critical(__('Something went wrong with block or page %1', $id));
        }
    }
}
