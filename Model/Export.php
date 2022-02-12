<?php

namespace Overdose\CMSContent\Model;

use Magento\Framework\Filesystem\Io\File;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;
use Overdose\CMSContent\Api\ContentExportInterface;
use ZipArchive;

/**
 *
 */
class Export implements ContentExportInterface
{
    const FILENAME = 'cms';
    const MEDIA_ARCHIVE_PATH = 'media';

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var CmsEntityConverterManagerInterface
     */
    private $cmsEntityConverterManager;
    /**
     * @var CmsEntityGeneratorManagerInterface
     */
    private $cmsEntityGeneratorManager;
    /**
     * @var File
     */
    private $file;

    public function __construct(
        CmsEntityConverterManagerInterface $cmsEntityConverterManager,
        CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager,
        Filesystem $filesystem,
        File $file
    ) {
        $this->cmsEntityConverterManager = $cmsEntityConverterManager;
        $this->cmsEntityGeneratorManager = $cmsEntityGeneratorManager;
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * @inheridoc
     */
    public function createZipFile(array $cmsEntities, string $type, string $fileName, bool $split): string
    {
        $exportPath = $this->filesystem->getExportPath();
        $relativeZipFile = Filesystem::EXPORT_PATH . '/' . $fileName;
        $converter = $this->cmsEntityConverterManager
            ->setEntities($cmsEntities)
            ->getConverter();
        $contentArray = $converter->convertToArray($cmsEntities);
        $zipArchive = $this->putContentToZip(
            $contentArray,
            $converter->getCmsEntityCode(),
            $exportPath . '/' . $fileName,
            $type,
            $split
        );

        // Add media files
        foreach ($contentArray['media'] as $mediaFile) {
            //Strip Quotes if any
            $mediaFile = str_replace(['"',"&quot;","'"], '', $mediaFile);
            $absMediaPath = $this->filesystem->getMediaPath($mediaFile);
            if ($this->file->fileExists($absMediaPath, true)) {
                $zipArchive->addFile($absMediaPath, self::MEDIA_ARCHIVE_PATH . '/' . $mediaFile);
            }
        }

        $zipArchive->close();

        // Clear export path
        $this->file->rm($exportPath);

        return $relativeZipFile;
    }

    /**
     * @param array $contentArray
     * @param string $cmsEntityCode
     * @param string $zipFileName
     * @param string $type
     * @param bool $split
     * @return ZipArchive
     */
    private function putContentToZip(
        array $contentArray,
        string $cmsEntityCode,
        string $zipFileName,
        string $type,
        bool $split)
    : ZipArchive {
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipFileName, ZipArchive::CREATE);
        if ($split) {
            foreach ($contentArray[$cmsEntityCode] as $key => $content) {
                $payload = $this->cmsEntityGeneratorManager
                    ->getGenerator($type)
                    ->generate([
                        $cmsEntityCode => [$key => $content]
                    ]);
                $zipArchive->addFromString(
                    sprintf('%s_%s_%s.%s', self::FILENAME, $this->prepareEntityPartName($cmsEntityCode), $key, $type),
                    $payload
                );
            }
        } else {
            $fullContent = [];
            foreach ($contentArray[$cmsEntityCode] as $key => $content) {
                $entityContent = [
                    $cmsEntityCode => [$key => $content]
                ];
                $fullContent = array_merge_recursive($fullContent, $entityContent);
            }
            $payload = $this->cmsEntityGeneratorManager
                ->getGenerator($type)
                ->generate($fullContent);
            $zipArchive->addFromString(
                sprintf('%s_%s.%s', self::FILENAME, $this->prepareEntityPartName($cmsEntityCode), $type),
                $payload
            );
        }

        return $zipArchive;
    }

    /**
     * @param string $cmsEntityCode
     * @return string
     */
    private function prepareEntityPartName(string $cmsEntityCode)
    {
        return substr($cmsEntityCode, 0, strlen($cmsEntityCode) - 1) . '_data';
    }
}
