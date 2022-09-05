<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Block\Adminhtml\Cms;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Overdose\CMSContent\Block\Adminhtml\Cms\Block\Edit\History as BlockHistory;
use Overdose\CMSContent\Block\Adminhtml\Cms\CmsAbstract;
use Overdose\CMSContent\Block\Adminhtml\Cms\Page\Edit\History as PageHistory;
use Overdose\CMSContent\Model\BackupManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CmsAbstractTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;
    /**
     * @var BlockRepositoryInterface|MockObject
     */
    private $blockRepositoryMock;
    /**
     * @var PageRepositoryInterface|MockObject
     */
    private $pageRepositoryMock;
    /**
     * @var BlockInterface|MockObject
     */
    private $blockModelMock;
    /**
     * @var PageInterface|MockObject
     */
    private $pageModelMock;
    /**
     * @var BackupManager|MockObject
     */
    private $backupManagerMock;
    /**
     * @var Context|MockObject
     */
    private $contextMock;
    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * Initialize test
     */
    public function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->blockRepositoryMock = $this->getMockBuilder(BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->pageRepositoryMock = $this->getMockBuilder(PageRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->blockModelMock = $this->getMockBuilder(BlockInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->pageModelMock = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backupManagerMock = $this->createMock(BackupManager::class);
        $this->contextMock = $this->createMock(Context::class);
    }

    /**
     * @dataProvider getBackupsDataProvider
     * @param string $urlParamId
     * @param string $cmsTypeId
     * @throws LocalizedException
     */
    public function testGetBackups(string $urlParamId, string $cmsTypeId)
    {
        $id = 1;
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->with($urlParamId)
            ->willReturn($id);

        if ($urlParamId == 'block_id') {
            $this->blockRepositoryMock->expects($this->atLeastOnce())
                ->method('getById')
                ->with($id)
                ->willReturn($this->blockModelMock);
            $this->backupManagerMock->expects($this->atLeastOnce())
                ->method('getBackupsByCmsEntity')
                ->with($cmsTypeId, $this->blockModelMock)
                ->willReturn([]);
            $this->contextMock->expects($this->atLeast(1))
                ->method('getRequest')
                ->willReturn($this->requestMock);

            $blockHistory = new BlockHistory(
                $this->contextMock,
                $this->blockRepositoryMock,
                $this->pageRepositoryMock,
                $this->backupManagerMock,
                $this->urlMock
            );

            $this->assertIsArray($blockHistory->getBackups());
        } elseif ($urlParamId == 'page_id') {
            $this->pageRepositoryMock->expects($this->atLeastOnce())
                ->method('getById')
                ->with($id)
                ->willReturn($this->pageModelMock);
            $this->backupManagerMock->expects($this->atLeastOnce())
                ->method('getBackupsByCmsEntity')
                ->with($cmsTypeId, $this->pageModelMock)
                ->willReturn([]);

            $this->contextMock->expects($this->atLeast(1))
                ->method('getRequest')
                ->willReturn($this->requestMock);

            $pageHistory = new PageHistory(
                $this->contextMock,
                $this->blockRepositoryMock,
                $this->pageRepositoryMock,
                $this->backupManagerMock,
                $this->urlMock
            );

            $this->assertIsArray($pageHistory->getBackups());
        }
    }

    /**
     * Data provider for testGetBackups. Exception cases are not included.
     * @return string[][]
     */
    public function getBackupsDataProvider(): array
    {
        return [
            'case_1_block_success' => ['block_id', 'cms_block'],
            'case_2_page_success' => ['page_id', 'cms_page']
        ];
    }

    /**
     * Test if the return object is an instance of Block or Page interface.
     * @dataProvider getCmsObjectDataProvider
     * @param int $id
     * @param string $bcType
     */
    public function testGetCmsObject(int $id, string $bcType)
    {
        if ($bcType == BackupManager::TYPE_CMS_BLOCK) {
            $this->blockRepositoryMock->expects($this->atLeastOnce())
                ->method('getById')
                ->with($id)
                ->willReturn($this->blockModelMock);
            $blockHistory = new BlockHistory(
                $this->contextMock,
                $this->blockRepositoryMock,
                $this->pageRepositoryMock,
                $this->backupManagerMock,
                $this->urlMock
            );
            $this->assertInstanceOf(BlockInterface::class, $blockHistory->getCmsObject($id));
        } elseif ($bcType == BackupManager::TYPE_CMS_PAGE) {
            $this->pageRepositoryMock->expects($this->atLeastOnce())
                ->method('getById')
                ->with($id)
                ->willReturn($this->pageModelMock);
            $pageHistory = new PageHistory(
                $this->contextMock,
                $this->blockRepositoryMock,
                $this->pageRepositoryMock,
                $this->backupManagerMock,
                $this->urlMock
            );
            $this->assertInstanceOf(PageInterface::class, $pageHistory->getCmsObject($id));
        }
    }

    /**
     * @return array[]
     */
    public function getCmsObjectDataProvider()
    {
        return [
            'case_1_block' => [1, 'cms_block'],
            'case_2_page' => [1, 'cms_page']
        ];
    }

    /**
     * Test
     * @return void
     */
    public function testGetBackupUrl()
    {
        $backup = [
            'identifier' => '1',
            'name' => 'TestBackup',
            'store_id' => '0'
        ];
        $requestData = [
            'bc_type' => 'cms_block',
            'bc_identifier' => $backup['identifier'],
            'item' => $backup['name'],
            'store_id' => $backup['store_id']
        ];
        $this->urlMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('cmscontent/history/view', $requestData)
            ->willReturn('somestring');

        $cmsAbstract = new CmsAbstract(
            $this->contextMock,
            $this->blockRepositoryMock,
            $this->pageRepositoryMock,
            $this->backupManagerMock,
            $this->urlMock
        );
        $reflectionClass = new ReflectionClass($cmsAbstract);
        $reflection_property = $reflectionClass->getProperty('bcType');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($cmsAbstract, 'cms_block');

        $this->assertSame('somestring', $cmsAbstract->getBackupUrl($backup));
    }
}
