<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Entity;

use SRIO\RestUploadBundle\Model\UploadableFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media implements UploadableFileInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $mimeType;

    /**
     * @var string
     */
    public $path;

    /**
     * @var integer
     */
    public $size;

    /**
     * @var string
     */
    public $originalName;

    /**
     * Set uploaded file.
     *
     */
    public function setFile (UploadedFile $file)
    {
        $this->mimeType = $file->getClientMimeType();
        $this->path = $file->getPathname();
        $this->size = $file->getClientSize();
        $this->originalName = $file->getClientOriginalName();
    }
}