<?php
namespace SRIO\RestUploadBundle\Request;

interface RequestContentHandlerInterface
{
    /**
     * Get a line.
     *
     * @return string
     */
    public function gets ();

    /**
     * Is the end of file.
     *
     * @return boolean
     */
    public function eof ();

    /**
     * Get cursor position.
     *
     * @return int
     */
    public function getCursor();
}
