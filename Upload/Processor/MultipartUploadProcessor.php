<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class MultipartUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @param Request $request
     */
    public function handleRequest (Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request);

        // Get formData
        $formData = $this->getFormData($request);
        $formData = $this->createFormData($formData);

        // Submit form data
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return;
        }

        // Handle the file content
        $file = $this->openFile();
        list($contentType, $content) = $this->getContent($request);

        try {
            $contentLength = $this->writeFile($file, 0, null, $content);
            $this->closeFile($file);

            // Create the uploaded file
            $uploadedFile = new UploadedFile(
                $file['path'],
                null,
                $contentType,
                $contentLength
            );

            $this->setUploadedFile($uploadedFile);
        } catch (UploadException $e) {
            $this->closeFile($file);
            $this->unlinkFile($file);

            throw $e;
        }
    }

    /**
     * Get the form data from the request.
     *
     * @param Request $request
     * @return array
     */
    protected function getFormData (Request $request)
    {
        list($boundaryContentType, $boundaryContent) = $this->getPart($request, 1);

        $expectedContentType = 'application/json';
        if (substr($boundaryContentType, 0, strlen($expectedContentType)) != $expectedContentType) {
            throw new UploadProcessorException(sprintf(
                'Expected content type of first part is %s. Found %s',
                $expectedContentType,
                $boundaryContentType
            ));
        }

        $jsonContent = json_decode($boundaryContent, true);
        if ($jsonContent === null) {
            throw new UploadProcessorException('Unable to parse JSON');
        }

        return $jsonContent;
    }

    /**
     * Get the content part of the request.
     *
     * @param Request $request
     */
    protected function getContent (Request $request)
    {
        return $this->getPart($request, 2);
    }

    /**
     * Check multipart headers.
     *
     * @param Request $request
     * @throws \SRIO\RestUploadBundle\Exception\UploadException
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     */
    protected function checkHeaders (Request $request, array $headers = array())
    {
        list($contentType, $boundary) = $this->parseContentTypeAndBoundary($request);

        $expectedContentType = 'multipart/related';
        if ($contentType != $expectedContentType) {
            throw new UploadProcessorException(sprintf(
                'Content-Type must be %s',
                $expectedContentType
            ));
        }

        parent::checkHeaders($request, array('Content-Length', 'Content-Length'));
    }

    /**
     * Get a part of request.
     *
     * @param Request $request
     * @param $part
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     */
    protected function getPart (Request $request, $part)
    {
        list($contentType, $boundary) = $this->parseContentTypeAndBoundary($request);

        $content = null;
        try {
            $handle = $request->getContent(true);
            $content = $this->getResourcePart($handle, $boundary, $part);
        } catch (\LogicException $e) {
            $handle = $request->getContent(false);
            if (!$handle) {
                throw new UploadProcessorException('Unable to read PHP input stream');
            }

            $content = $this->getStringPart($handle, $boundary, $part);
        }

        if (empty($content)) {
            throw new UploadProcessorException(sprintf('An empty content found for part %d', $part));
        }

        $headerLimitation = strpos($content, PHP_EOL.PHP_EOL);
        if ($headerLimitation == -1) {
            throw new UploadProcessorException('Unable to determine headers limit');
        }

        $delimiter = '--'.$boundary;
        $contentType = null;
        $headersOffset = strlen($delimiter);
        $headersContent = substr($content, $headersOffset, $headerLimitation - $headersOffset);
        $headersContent = trim($headersContent);
        $body = substr($content, $headerLimitation);
        $body = trim($body);

        foreach (explode(PHP_EOL, $headersContent) as $header) {
            $parts = explode(':', $header);
            if (count($parts) != 2) {
                continue;
            }

            $name = trim($parts[0]);
            if (strtolower($name) == 'content-type') {
                $contentType = trim($parts[1]);
                break;
            }
        }

        return array($contentType, $body);
    }

    /**
     * Get part of a resource.
     *
     * @param $resource
     * @param int $part
     * @return string
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     */
    protected function getResourcePart ($resource, $boundary, $part)
    {
        $delimiter = '--'.$boundary.PHP_EOL;
        $boundaryCount = 0;
        $content = '';
        while (!feof($resource)) {
            $line = fgets($resource);
            if ($line === false) {
                throw new UploadProcessorException('An error appears while reading input');
            } else if (empty($line) || $line == PHP_EOL) {
                continue;
            }

            if ($boundaryCount == 0) {
                if ($line != $delimiter) {
                    throw new UploadProcessorException('Expected boundary delimiter');
                }

                $boundaryCount++;
            } else if ($line == $delimiter) {
                $boundaryCount++;

                if ($boundaryCount > $part) {
                    break;
                }
            }

            if ($boundaryCount == $part) {
                $content .= $line;
            }
        }

        return trim($content);
    }

    /**
     * Get part of a string.
     *
     * @param $string
     * @param $boudary
     * @param $part
     */
    protected function getStringPart ($string, $boundary, $part)
    {
        $delimiter = '--'.$boundary.PHP_EOL;
        $boundaryCount = 0;
        $offset = 0;
        $content = '';
        while ($offset != -1) {
            $next = strpos($string, PHP_EOL, $offset);
            if ($next < 0 || $next === false) {
                break;
            }

            $length = $next - $offset + 1;
            $line = substr($string, $offset, $length);
            $offset = $next + 1;

            if ($boundaryCount == 0) {
                if ($line != $delimiter) {
                    throw new UploadProcessorException('Expected boundary delimiter');
                }

                $boundaryCount++;
            } else if ($line == $delimiter) {
                $boundaryCount++;

                if ($boundaryCount > $part) {
                    break;
                }
            }

            if ($boundaryCount == $part) {
                $content .= $line;
            }
        }

        return trim($content);
    }

    /**
     * Parse the content type and boudary from Content-Type header.
     *
     * @param Request $request
     * @return array
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     */
    protected function parseContentTypeAndBoundary (Request $request)
    {
        $contentParts = explode(';', $request->headers->get('Content-Type'));
        if (count($contentParts) != 2) {
            throw new UploadProcessorException('Boundary may be missing');
        }

        $contentType = trim($contentParts[0]);
        $boundaryPart = trim($contentParts[1]);

        $shouldStart = 'boundary="';
        if (substr($boundaryPart, 0, strlen($shouldStart)) != $shouldStart || substr($boundaryPart, -1) != '"') {
            throw new UploadProcessorException('Boundary is not set');
        }

        $boundary = substr($boundaryPart, strlen($shouldStart), -1);
        return array($contentType, $boundary);
    }
}