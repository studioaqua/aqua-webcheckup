<?php
/**
 *
 */
namespace Aqua\WebCheckupBundle\Entity;

class Website
{
    protected $website;

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
    }

}
