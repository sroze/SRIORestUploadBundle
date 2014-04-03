<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Upload\File\FileWriter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     * @param Request $request
     * @return boolean|Response
     */
    public function handleRequest (Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request, array('Content-Length', 'Content-Type'));

        // Submit form data
        $formData = $this->createFormData($request->query->all());
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return false;
        }

        // Handle the file content
        $length = (int) $request->headers->get('Content-Length');
        $filePath = $this->createFilePath();
        $writer = new FileWriter($filePath);

        try {
            $writer->write($request->getContent(), $length);
            $writer->close();

            // Create the uploaded file
            $uploadedFile = new UploadedFile(
                $filePath,
                null,
                $request->headers->get('Content-Type'),
                $request->headers->get('Content-Length')
            );

            $this->setUploadedFile($uploadedFile);

            return true;
        } catch (UploadException $e) {
            $writer->unlink();

            throw $e;
        }
    }
}