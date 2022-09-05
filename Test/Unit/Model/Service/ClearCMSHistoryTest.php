<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Model\Service;

/**
 * Override filemtime() in the Overdose\CMSContent\Model\Service namespace when testing
 *
 * @return int
 */
function filemtime()
{
    return 123;
}

namespace Overdose\CMSContent\Test\Unit\Model\Service;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Overdose\CMSContent\Model\Config;
use Overdose\CMSContent\Model\Service\ClearCMSHistory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClearCMSHistoryTest extends TestCase
{
    /**
     * @var File|MockObject
     */
    private $fileMock;
    /**
     * @var ClearCMSHistory|MockObject
     */
    private $serviceModel;
    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;
    /**
     * @var Config|MockObject
     */
    private $configMock;
    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerInterfaceMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerInterfaceMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceModel = new ClearCMSHistory(
            $this->dateTimeMock,
            $this->configMock,
            $this->loggerInterfaceMock,
            $this->fileMock
        );
    }

    /**
     * Test clear action by providing two files in mock objects and expecting count of two returned in result.
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testExecute()
    {
        $outerDirs = ['parentOne', 'parentTwo'];
        $files = ['fileOne', 'fileTwo'];

        $this->fileMock->expects($this->atLeastOnce())
            ->method('readDirectory')
            ->willReturnOnConsecutiveCalls($outerDirs, $files, $files, $files, $files);

        $this->fileMock->expects($this->atLeastOnce())
            ->method('isFile')
            ->willReturn(true);

        $this->configMock->expects($this->atLeastOnce())
            ->method('getBackupsDir')
            ->willReturn('');

        $this->configMock->expects($this->atLeastOnce())
            ->method('getMethodType')
            ->willReturn(Config::PERIOD);

        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('gmtTimestamp')
            ->willReturn(1659355018);

        $this->assertEquals(2, $this->serviceModel->execute(Config::TYPE_BLOCK));
    }
}
