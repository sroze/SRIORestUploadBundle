<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SimpleUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     * @param Request $request
     */
    public function handleRequest (Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request, array('Content-Length', 'Content-Length'));

        // Submit form data
        $formData = $this->createFormData($request->query->all());
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return;
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