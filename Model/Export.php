<?php


namespace Overdose\CMSContent\Model;


use Magento\Framework\Filesystem\Io\File;
use Overdose\CMSContent\Api\CmsEntityConverterManagerInterface;
use Overdose\CMSContent\Api\CmsEntityGeneratorManagerInterface;
use Overdose\CMSContent\Api\ContentExportInterface;

class Export implements ContentExportInterface
{
    const FILENAME = 'cms';
    const MEDIA_ARCHIVE_PATH = 'media';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var CmsEntityConverterManagerInterface
     */
    private CmsEntityConverterManagerInterface $cmsEntityConverterManager;
    /**
     * @var CmsEntityGeneratorManagerInterface
     */
    private CmsEntityGeneratorManagerInterface $cmsEntityGeneratorManager;
    /**
     * @var File
     */
    private File $file;

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
     * Create a zip file and return its name
     * @param \Magento\Cms\Api\Data\PageInterface[] | \Magento\Cms\Api\Data\BlockInterface[] $cmsEntities
     * @param string $type
     * @param string $fileName
     * @return string
     */
    public function createZipFile(array $cmsEntities, string $type, string $fileName = null): string
    {
        $exportPath = $this->filesystem->getExportPath();

        $zipFile = $exportPath . '/' . $fileName;
        $relativeZipFile = Filesystem::EXPORT_PATH . '/' . $fileName;

        $zipArchive = new \ZipArchive();
        $zipArchive->open($zipFile, \ZipArchive::CREATE);

        $converter = $this->cmsEntityConverterManager
            ->setEntities($cmsEntities)
            ->getConverter();
        $contentArray = $converter->convertToArray($cmsEntities);

        $cmsEntityCode = $converter->getCmsEntityCode();
        foreach ($contentArray[$cmsEntityCode] as $key => $content) {
            $payload = $this->cmsEntityGeneratorManager
                ->getGenerator($type)
                ->generate([
                    $cmsEntityCode => [$key => $content],
                    'media' => $contentArray['media']
                ]);
            $zipArchive->addFromString(sprintf('%s-%s-%s.%s', self::FILENAME, $cmsEntityCode, $key, $type), $payload);
        }

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
        $this->file->rm($exportPath, true);

        return $relativeZipFile;
    }
}
