<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;
use Overdose\CMSContent\Api\ContentExportInterface;
use Overdose\CMSContent\Controller\Adminhtml\Page\MassExport;
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
     * @var DateTime|MockObject
     */
    private $dateTimeMock;
    /**
     * @var CmsEntityConverterManagerInterface|MockObject
     */
    private $cmsEntityConverterManagerMock;
    /**
     * @var CmsEntityConverterInterface|MockObject
     */
    private $cmsEntityConverterMock;
    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;
    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;
    /**
     * @var AbstractDb|MockObject
     */
    private $abstractDbMock;
    /**
     * @var Context|MockObject
     */
    private $contextMock;
    /**
     * @var ContentExportInterface|MockObject
     */
    private $contentExportMock;
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    public function setUp(): void
    {
        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsEntityConverterManagerMock = $this->getMockBuilder(CmsEntityConverterManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cmsEntityConverterMock = $this->getMockBuilder(CmsEntityConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsPageModelMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentExportMock = $this->getMockBuilder(ContentExportInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    public function testExecute()
    {
        $items = [$this->cmsPageModelMock, $this->cmsPageModelMock];

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->collectionMock)
            ->willReturn($this->abstractDbMock);

        $this->abstractDbMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->with('Ymd_His');

        $this->cmsEntityConverterManagerMock->expects($this->once())
            ->method('getConverter')
            ->with(CmsEntityConverterManagerInterface::PAGE_ENTITY_CODE)
            ->willReturn($this->cmsEntityConverterMock);

        $this->cmsEntityConverterMock->expects($this->once())
            ->method('convertToArray')
            ->with($items)
            ->willReturn([]);

        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->responseMock);

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $controller = new MassExport(
            $this->contextMock,
            $this->filterMock,
            $this->contentExportMock,
            $this->collectionFactoryMock,
            $this->fileFactoryMock,
            $this->dateTimeMock,
            $this->cmsEntityConverterManagerMock
        );

        $this->assertSame($this->responseMock, $controller->execute());
    }
}
