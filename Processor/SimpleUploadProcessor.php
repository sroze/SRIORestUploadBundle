<?php
namespace SRIO\RestUploadBundle\Processor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use SRIO\RestUploadBundle\Upload\UploadResult;

class SimpleUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     * @param Request $request
     * @return UploadResult
     */
    public function handleRequest (Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request, array('Content-Length', 'Content-Type'));

        $result = new UploadResult();
        $result->setForm($this->form);
        $result->setRequest($request);
        $result->setConfig($this->config);

        // Submit form data
        if ($this->form !== null) {
            $formData = $this->createFormData($request->query->all());
            $this->form->submit($formData);
        }

        if ($this->form == null || $this->form->isValid()) {
            $content = $request->getContent();
            $file = $this->storageHandler->store($result, $content);
            $result->setFile($file);
        }

        return $result;
    }
}