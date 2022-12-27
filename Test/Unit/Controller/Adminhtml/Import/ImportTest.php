<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Overdose\CMSContent\Api\ContentImportInterface;
use Overdose\CMSContent\Controller\Adminhtml\Import\Import;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;
    /**
     * @var ContentImportInterface|MockObject
     */
    private $importExportMock;
    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;
    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectFactoryMock;
    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->importExportMock = $this->getMockBuilder(ContentImportInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->redirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testExecute()
    {
        $upload[] = [
            'file' => 'filename',
            'path' => 'temppath'
        ];
        $cmsMode = ContentImportInterface::OD_CMS_MODE_UPDATE;
        $mediaMode = ContentImportInterface::OD_MEDIA_MODE_NONE;

        $this->requestMock->expects($this->atLeast(3))
            ->method('getParam')
            ->withConsecutive(['cms_import_mode'], ['media_import_mode'], ['upload'])
            ->willReturnOnConsecutiveCalls(
                $cmsMode,
                $mediaMode,
                $upload
            );

        $this->importExportMock->expects($this->once())
            ->method('setCmsModeOption')
            ->with($cmsMode)
            ->willReturn($this->importExportMock);

        $this->importExportMock->expects($this->once())
            ->method('setMediaModeOption')
            ->with($mediaMode)
            ->willReturn($this->importExportMock);

        $this->importExportMock->expects($this->once())
            ->method('importContentFromZipFile')
            ->with($upload[0]['path'] . DIRECTORY_SEPARATOR . $upload[0]['file'], true)
            ->willReturn(1);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage');

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);

        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $controller = new Import(
            $this->contextMock,
            $this->importExportMock,
            $this->redirectFactoryMock
        );

        $this->assertSame($this->redirectMock, $controller->execute());
    }
}
