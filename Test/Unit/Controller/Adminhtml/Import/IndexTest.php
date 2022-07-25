<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Overdose\CMSContent\Controller\Adminhtml\Import\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;
    /**
     * @var Page|MockObject
     */
    private $resultPageMock;
    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;
    /**
     * @var Title|MockObject
     */
    private $pageTitleMock;
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    public function setUp(): void
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testExecute()
    {
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($this->resultPageMock);

        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Overdose_CMSContent::import')
            ->willReturnSelf();

        $this->resultPageMock->expects($this->atLeast(2))
            ->method('addBreadcrumb')
            ->withConsecutive(['CMS', 'CMS'], ['Import CMS', 'Import CMS'])
            ->willReturnSelf();

        $this->resultPageMock->expects($this->atLeast(2))
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);

        $this->pageConfigMock->expects($this->atLeast(2))
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->pageTitleMock->expects($this->atLeast(2))
            ->method('prepend')
            ->withConsecutive(['CMS'], ['CMS Import']);

        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $controller = new Index($this->contextMock);

        $this->assertSame($this->resultPageMock, $controller->execute());

    }
}
