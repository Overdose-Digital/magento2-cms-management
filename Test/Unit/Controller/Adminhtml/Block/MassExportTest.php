<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\ResourceModel\Block\Collection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;
use Overdose\CMSContent\Api\ContentExportInterface;
use Overdose\CMSContent\Controller\Adminhtml\Block\MassExport;
use Overdose\CMSContent\Model\Content\Converter\CmsEntityConverterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassExportTest extends TestCase
{
    /**
     * @var Filter|MockObject
     */
    private $filterMock;
    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;
    /**
     * @var Collection|MockObject
     */
    private $collectionMock;
    /**
     * @var AbstractDb|MockObject
     */
    private $abstractDbMock;
    /**
     * @var CmsEntityConverterManagerInterface|MockObject
     */
    private $cmsEntityConverterMangerMock;
    /**
     * @var Block|MockObject
     */
    private $cmsBlockModelMock;
    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;
    /**
     * @var CmsEntityConverterInterface|MockObject
     */
    private $cmsEntityConverterMock;
    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;
    /**
     * @var Context|MockObject
     */
    private $contextMock;
    /**
     * @var ContentExportInterface|MockObject
     */
    private $contentExportMock;
    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsEntityConverterMangerMock = $this->getMockBuilder(CmsEntityConverterManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cmsBlockModelMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsEntityConverterMock = $this->getMockBuilder(CmsEntityConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentExportMock = $this->getMockBuilder(ContentExportInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Test execute function to see if the returned object is an instance of responseMock
     * @return void
     * @throws LocalizedException
     */
    public function testExecute()
    {
        $items = [$this->cmsBlockModelMock, $this->cmsBlockModelMock];

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->collectionMock)
            ->willReturn($this->abstractDbMock);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->abstractDbMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->with('Ymd_His');

        $this->cmsEntityConverterMangerMock->expects($this->once())
            ->method('getConverter')
            ->with(CmsEntityConverterManagerInterface::BLOCK_ENTITY_CODE)
            ->willReturn($this->cmsEntityConverterMock);

        $this->cmsEntityConverterMock->expects($this->once())
            ->method('convertToArray')
            ->with($items)
            ->willReturn([]);

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->responseMock);

        $controller = new MassExport(
            $this->contextMock,
            $this->filterMock,
            $this->contentExportMock,
            $this->collectionFactoryMock,
            $this->fileFactoryMock,
            $this->dateTimeMock,
            $this->cmsEntityConverterMangerMock
        );

        $this->assertSame($this->responseMock, $controller->execute());
    }
}
