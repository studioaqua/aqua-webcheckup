<?php
/**
 *
 */
namespace Aqua\WebCheckupBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Website
{

  /**
   * @Assert\Url()
   */
  protected $website;

  protected $name;
  protected $flash = FALSE;
  protected $responsive = FALSE;
  protected $rank = 0;
  protected $htmlSource;


  public function getWebsite()
  {
      return $this->website;
  }

  public function setWebsite($website)
  {
      $this->website = $website;
  }

  public function getName()
  {
      return $this->name;
  }

  public function setName($name)
  {
      $this->name = $name;
  }

  public function isFlash()
  {
      return $this->flash;
  }

  public function setFlash($flash)
  {
      $this->flash = $flash;
  }

  public function isResponsive()
  {
      return $this->responsive;
  }

  public function setResponsive($responsive)
  {
      $this->responsive = $responsive;
  }

  public function getRank()
  {
      return $this->rank;
  }

  public function setRank($rank)
  {
      $this->rank = $rank;
  }

  public function getHtmlSource()
  {
      return $this->htmlSource;
  }

  public function sethHtmlSource($htmlSource)
  {
      $this->htmlSource = $htmlSource;
  }

}
