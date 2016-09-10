<?php

namespace SRIO\RestUploadBundle\Processor;

use SRIO\RestUploadBundle\Storage\FileStorage;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Upload\UploadResult;

class FormDataUploadProcessor extends SimpleUploadProcessor
{
    const KEY_FIELD_FILE = 'key_file';
    const KEY_FIELD_FORM = 'key_form';

    /**
     * {@inheritdoc}
     */
    public function handleUpload(Request $request, FormInterface $form = null, array $config = array())
    {
        $config = array_merge(array(
            self::KEY_FIELD_FILE => 'file',
            self::KEY_FIELD_FORM => 'form',
        ), $config);

        return parent::handleUpload($request, $form, $config);
    }

    /**
     * @param Request $request
     *
     * @return \SRIO\RestUploadBundle\Upload\UploadResult
     *
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     */
    public function handleRequest(Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request, array('Content-Length', 'Content-Type'));
        if (!$request->files->has($this->config[self::KEY_FIELD_FILE])) {
            throw new UploadException(sprintf('%s file not found', $this->config[self::KEY_FIELD_FILE]));
        }

        $response = new UploadResult();
        $response->setRequest($request);
        $response->setConfig($this->config);

        if ($this->form != null) {
            $response->setForm($this->form);

            if (!$request->request->has($this->config[self::KEY_FIELD_FORM])) {
                throw new UploadException(sprintf(
                    '%s request field not found in (%s)',
                    $this->config[self::KEY_FIELD_FORM],
                    implode(', ', $request->request->keys())
                ));
            }

            $submittedValue = $request->request->get($this->config[self::KEY_FIELD_FORM]);
            if (is_string($submittedValue)) {
                $submittedValue = json_decode($submittedValue, true);
                if (!$submittedValue) {
                    throw new UploadException('Unable to decode JSON');
                }
            } elseif (!is_array($submittedValue)) {
                throw new UploadException('Unable to parse form data');
            }

            // Submit form data
            $formData = $this->createFormData($submittedValue);
            $this->form->submit($formData);
            if (!$this->form->isValid()) {
                return $response;
            }
        }

        /** @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
        $uploadedFile = $request->files->get($this->config[self::KEY_FIELD_FILE]);
        $contents = file_get_contents($uploadedFile->getPathname());
        $file = $this->storageHandler->store($response, $contents, array(
            'metadata' => array(
                FileStorage::METADATA_CONTENT_TYPE => $uploadedFile->getMimeType(),
            ),
        ));

        $response->setFile($file);

        return $response;
    }
}
