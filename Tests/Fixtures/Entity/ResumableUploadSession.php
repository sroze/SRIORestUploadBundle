<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Entity;

use SRIO\RestUploadBundle\Entity\ResumableUploadSession as BaseResumableUploadSession;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="resumable_upload_session")
 */
class ResumableUploadSession extends BaseResumableUploadSession
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
}
