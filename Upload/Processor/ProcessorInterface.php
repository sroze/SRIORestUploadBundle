<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Upload processor interface.
 *
 */
interface ProcessorInterface
{
    /**
     * Handle the upload request.
     *
     * @param Request $request
     * @param FormInterface $form
     * @param array $options
     * @return boolean|Response
     */
    public function handleUpload (Request $request, FormInterface $form, array $options);
}