<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Entity;

use SRIO\RestUploadBundle\Model\UploadableFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="media")
 */
class Media implements UploadableFileInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $mimeType;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    public $path;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    public $size;

    /**
     * @ORM\Column(type="string")
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