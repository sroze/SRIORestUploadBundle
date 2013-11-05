<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ResumableUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     * @param Request $request
     */
    public function handleRequest (Request $request)
    {

    }
}