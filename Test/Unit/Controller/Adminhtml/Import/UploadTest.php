<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Controller\Adminhtml\Import;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Overdose\CMSContent\Controller\Adminhtml\Import\Upload;
use Overdose\CMSContent\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;
    /**
     * @var Json|MockObject
     */
    private $resultJsonMock;
    /**
     * @var UploaderFactory|MockObject
     */
    private $uploaderFactoryMock;
    /**
     * @var Config|MockObject
     */
    private $configMock;
    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;
    /**
     * @var Uploader|MockObject
     */
    private $uploaderMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderMock = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $backupDir = '';

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);

        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([])
            ->willReturnSelf();

        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => 'upload'])
            ->willReturn($this->uploaderMock);

        $this->uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['zip'])
            ->willReturnSelf();

        $this->uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true)
            ->willReturnSelf();

        $this->uploaderMock->expects($this->once())
            ->method('save')
            ->with($backupDir . DIRECTORY_SEPARATOR . 'import')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('getBackupsDir')
            ->willReturn($backupDir);

        $controller = new Upload(
            $this->configMock,
            $this->resultJsonFactoryMock,
            $this->uploaderFactoryMock,
            $this->messageManagerMock
        );

        $this->assertSame($this->resultJsonMock, $controller->execute());
    }
}
