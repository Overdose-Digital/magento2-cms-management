<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Model;

use Laminas\Json\Json;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\Page;
use Magento\Framework\Filesystem\Driver\File;
use Overdose\CMSContent\File\FileManagerInterface;
use Overdose\CMSContent\Model\BackupManager;
use Overdose\CMSContent\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BackupManagerTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;
    /**
     * @var FileManagerInterface|MockObject
     */
    private $fileMock;
    /**
     * @var File|MockObject
     */
    private $fileDriverMock;
    /**
     * @var Page|MockObject
     */
    private $cmsPageModelMock;
    /**
     * @var Block|MockObject
     */
    private $cmsBlockModelMock;
    /**
     * @var BackupManager
     */
    private $model;
    /**
     * @var Json
     */
    private Json $json;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileMock = $this->getMockBuilder(FileManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileDriverMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsBlockModelMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsPageModelMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->json = new Json();

        $this->model = new BackupManager(
            $this->fileDriverMock,
            $this->fileMock,
            $this->configMock,
            $this->json,
            $this->loggerMock
        );
    }

    /**
     * @return void
     */
    public function testCreateBackupWithBlock()
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->fileMock->expects($this->once())
            ->method('writeData');

        $this->cmsBlockModelMock->expects($this->atLeastOnce())
            ->method('getStores')
            ->willReturn([0, 1]);

        $this->cmsBlockModelMock->expects($this->atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('1');

        $this->cmsBlockModelMock->expects($this->atLeastOnce())
            ->method('getOrigData')
            ->withConsecutive(['title'], ['content'])
            ->willReturnOnConsecutiveCalls('Lorem Ipsum Dolor Sit Amet.');

        $this->model->setCmsObject($this->cmsBlockModelMock);
        $this->assertSame(
            $this->model,
            $this->model->createBackup('cms_block', $this->cmsBlockModelMock)
        );
    }

    /**
     * @return void
     */
    public function testCreateBackupWithPage()
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->fileMock->expects($this->once())
            ->method('writeData');

        $this->cmsPageModelMock->expects($this->atLeastOnce())
            ->method('getStores')
            ->willReturn([0, 1]);

        $this->cmsPageModelMock->expects($this->atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('1');

        $this->cmsPageModelMock->expects($this->atLeastOnce())
            ->method('getOrigData')
            ->withConsecutive(['title'], ['content'])
            ->willReturnOnConsecutiveCalls('Lorem Ipsum Dolor Sit Amet.');

        $this->model->setCmsObject($this->cmsPageModelMock);
        $this->assertSame(
            $this->model,
            $this->model->createBackup('cms_page', $this->cmsPageModelMock)
        );
    }
}
