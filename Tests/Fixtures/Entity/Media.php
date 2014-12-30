<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Entity;

use SRIO\RestUploadBundle\Model\UploadableFileInterface;
use SRIO\RestUploadBundle\Storage\UploadedFile;

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
    public function setFile(UploadedFile $uploaded)
    {
        $this->path = $uploaded->getFile()->getName();
        $this->size = $uploaded->getFile()->getSize();

        // TODO Add mimetype on `UploadedFile`
        $this->mimeType = $uploaded->getStorage()->getFilesystem()->mimeType($this->path);

        // TODO Add original name
        $this->originalName = $uploaded->getFile()->getName();
    }
}
