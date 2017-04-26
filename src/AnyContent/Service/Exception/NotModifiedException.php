<?php

namespace AnyContent\Service\Exception;

class NotModifiedException extends \Exception
{

    protected $etag;


    /**
     * @return mixed
     */
    public function getEtag()
    {
        return $this->etag;
    }


    /**
     * @param mixed $etag
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
    }

}