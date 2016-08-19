<?php

namespace SRIO\RestUploadBundle\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestContentHandler implements RequestContentHandlerInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $cursor;

    /**
     * @var string|resource
     */
    protected $content = null;

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->cursor = 0;
    }

    /**
     * Get a line.
     *
     * If false is return, it's the end of file.
     *
     * @return string|bool
     */
    public function gets()
    {
        $content = $this->getContent();
        if (is_resource($content)) {
            $line = fgets($content);
            $this->cursor = ftell($content);

            return $line;
        }

        $next = strpos($content, "\r\n", $this->cursor);
        $eof = $next < 0 || $next === false;

        if ($eof) {
            $line = substr($content, $this->cursor);
        } else {
            $length = $next - $this->cursor + strlen("\r\n");
            $line = substr($content, $this->cursor, $length);
        }

        $this->cursor = $eof ? -1 : $next + strlen("\r\n");

        return $line;
    }

    /**
     * @return int
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Is end of file ?
     *
     * @return bool
     */
    public function eof()
    {
        return $this->cursor == -1 || (is_resource($this->getContent()) && feof($this->getContent()));
    }

    /**
     * Get request content.
     *
     * @return resource|string
     *
     * @throws \RuntimeException
     */
    public function getContent()
    {
        if ($this->content === null) {
            try {
                $this->content = $this->request->getContent(true);
            } catch (\LogicException $e) {
                $this->content = $this->request->getContent(false);

                if (!$this->content) {
                    throw new \RuntimeException('Unable to get request content');
                }
            }
        }

        return $this->content;
    }
}
