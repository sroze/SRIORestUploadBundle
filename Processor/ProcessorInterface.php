<?php

namespace SRIO\RestUploadBundle\Processor;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Upload processor interface.
 */
interface ProcessorInterface
{
    /**
     * Handle the upload request.
     *
     * @param Request       $request
     * @param FormInterface $form
     * @param array         $options
     *
     * @return \SRIO\RestUploadBundle\Upload\UploadResult
     */
    public function handleUpload(Request $request, FormInterface $form = null, array $options = array());
}
