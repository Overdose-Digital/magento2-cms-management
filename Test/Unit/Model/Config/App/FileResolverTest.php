<?php

namespace Overdose\CMSContent\Test\Unit\Model\Config\App;

use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Overdose\CMSContent\Model\Config\App\FileResolver;
use Overdose\CMSContent\Model\Dir\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileResolverTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    private $readerMock;
    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;
    /**
     * @var FileIteratorFactory|MockObject
     */
    private $iteratorFactoryMock;
    /**
     * @var ReadInterface|MockObject
     */
    private $readInterfaceMock;
    /**
     * @var FileIterator|MockObject
     */
    private $fileIteratorMock;
    /**
     * @var FileResolver
     */
    private $sutObject;

    public function setUp(): void
    {
        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorFactoryMock = $this->getMockBuilder(FileIteratorFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->readInterfaceMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileIteratorMock = $this->getMockBuilder(FileIterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sutObject = new FileResolver(
            $this->readerMock,
            $this->fileSystemMock,
            $this->iteratorFactoryMock
        );
    }

    /**
     * @dataProvider dataProviderForGet
     */
    public function testGet($filename, $scope)
    {
        if ($scope == 'primary') {
            $this->fileSystemMock->expects($this->once())
                ->method('getDirectoryRead')
                ->willReturn($this->readInterfaceMock);

            $this->readInterfaceMock->expects($this->once())
                ->method('search')
                ->willReturn(['subFileOne', 'subFileTwo']);

            $this->readInterfaceMock->expects($this->exactly(2))
                ->method('getAbsolutePath')
                ->willReturnOnConsecutiveCalls(['absPathOne', 'absPathTwo']);

            $this->iteratorFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($this->fileIteratorMock);
        } else {
            $this->readerMock->expects($this->once())
                ->method('getConfigurationFiles')
                ->willReturn($this->fileIteratorMock);
        }

        $this->assertSame($this->fileIteratorMock, $this->sutObject->get($filename, $scope));
    }

    public function dataProviderForGet(): array
    {
        return [
            ['fileOne', ''],
            ['fileTwo', 'primary'],
            ['fileThree', 'global'],
        ];
    }
}
