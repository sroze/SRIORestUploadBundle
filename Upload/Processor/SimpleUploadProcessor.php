<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SimpleUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @param Request $request
     */
    public function handleRequest (Request $request)
    {
        // Submit form data
        $formData = $this->createFormData($request->query->all());
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return;
        }

        // Check that needed headers exists
        if (!$request->headers->has('Content-Type')) {
            throw new UploadException('Content-Type header is needed');
        } else if (!$request->headers->has('Content-Length')) {
            throw new UploadException('Content-Length header is needed');
        }

        // Handle the file content
        $length = (int) $request->headers->get('Content-Length');
        $file = $this->openFile();

        try {
            $this->writeFile($file, 0, $length, $request->getContent());
            $this->closeFile($file);

            // Create the uploaded file
            $uploadedFile = new UploadedFile(
                $file['path'],
                null,
                $request->headers->get('Content-Type'),
                $request->headers->get('Content-Length')
            );

            $this->setUploadedFile($uploadedFile);
        } catch (UploadException $e) {
            $this->closeFile($file);
            $this->unlinkFile($file);

            throw $e;
        }
    }
}