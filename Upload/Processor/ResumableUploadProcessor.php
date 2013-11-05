<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use Doctrine\ORM\EntityManager;
use SRIO\RestUploadBundle\Exception\UploadException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ResumableUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct (EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     * @param Request $request
     */
    public function handleRequest (Request $request)
    {

    }
}