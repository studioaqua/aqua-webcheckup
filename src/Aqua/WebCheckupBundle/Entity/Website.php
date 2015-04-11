<?php
/**
 *
 */
namespace AppBundle\Entity;

class Website
{
    protected $url;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

}
