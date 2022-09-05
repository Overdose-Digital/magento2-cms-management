<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Controller\Adminhtml\History;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Overdose\CMSContent\Controller\Adminhtml\History\View;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;
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
    /**
     * @var Page|MockObject
     */
    private $pageMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
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
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->once())
            ->method('prepend')
            ->with('Backup Preview');
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $controller = new View($this->contextMock);
        $this->assertSame($this->pageMock, $controller->execute());

    }
}
