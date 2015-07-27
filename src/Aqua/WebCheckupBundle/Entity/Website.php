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

  private $name;
  private $flash = FALSE;
  private $responsive = FALSE;
  private $rank = 0;
  private $htmlSource;


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
  public function isFlashLiteral()
  {
      return ($this->flash) ? 'Yes' : 'No';
  }

  public function setFlash($flash)
  {
      $this->flash = $flash;
  }

  public function isResponsive()
  {
      return $this->responsive;
  }
  public function isResponsiveLiteral()
  {
      return ($this->responsive) ? 'Yes' : 'No';
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
      $this->rank += $rank;
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
