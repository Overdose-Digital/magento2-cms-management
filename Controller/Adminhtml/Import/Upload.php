<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Overdose\CMSContent\Model\Config;

class Upload implements HttpPostActionInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param Config $config
     * @param JsonFactory $resultJsonFactory
     * @param UploaderFactory $fileUploaderFactory
     * @param ManagerInterface $messageManager
     * @codeCoverageIgnore
     */
    public function __construct(
        Config $config,
        JsonFactory $resultJsonFactory,
        UploaderFactory $fileUploaderFactory,
        ManagerInterface $messageManager
    ) {
        $this->config   = $config;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result   = $this->upload();
        $response = $this->resultJsonFactory->create();
        $response->setData($result);

        return $response;
    }

    /**
     * Upload file
     *
     * @return array
     */
    private function upload(): array
    {
        $result    = [];
        $uploader  = $this->fileUploaderFactory->create(['fileId' => 'upload']);
        $uploader->setAllowedExtensions(['zip']);
        $uploader->setAllowRenameFiles(true);

        try {
            $result = $uploader->save($this->config->getBackupsDir() . DIRECTORY_SEPARATOR . 'import');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $result;
    }
}
