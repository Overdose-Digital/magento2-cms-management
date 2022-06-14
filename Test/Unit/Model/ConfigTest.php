<?php

declare(strict_types=1);

namespace Overdose\CMSContent\Test\Unit\Model;

use Overdose\CMSContent\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var DirectoryList|MockObject
     */
    protected $directoryList;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * Initialize test
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->directoryList = $this->createMock(DirectoryList::class);

        $this->config = $this->createMock(Config::class);
    }

    /**
     * Test getBackupsDir
     *
     * @return void
     */
    public function testGetBackupsDir()
    {
        $this->directoryList->expects($this->once())
            ->method('getPath')
            ->willReturn('var');

        $config = new Config(
            $this->scopeConfig,
            $this->directoryList
        );

        $result = $config->getBackupsDir();

        $expected = 'var' . DIRECTORY_SEPARATOR . Config::CMS_DIR;

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getExportPath
     *
     * @return void
     */
    public function testGetExportPath()
    {
        $this->directoryList->expects($this->once())
            ->method('getPath')
            ->willReturn('var');

        $config = new Config(
            $this->scopeConfig,
            $this->directoryList
        );

        $result = $config->getExportPath();

        $expected = 'var' . DIRECTORY_SEPARATOR . Config::CMS_DIR . DIRECTORY_SEPARATOR . Config::EXPORT_PATH;

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getExtractPath
     *
     * @return void
     */
    public function testGetExtractPath()
    {
        $this->directoryList->expects($this->once())
            ->method('getPath')
            ->willReturn('var');

        $config = new Config(
            $this->scopeConfig,
            $this->directoryList
        );

        $result = $config->getExtractPath();

        $expected = 'var' . DIRECTORY_SEPARATOR . Config::CMS_DIR . DIRECTORY_SEPARATOR . Config::EXTRACT_PATH;

        $this->assertEquals($expected, $result);
    }
}
