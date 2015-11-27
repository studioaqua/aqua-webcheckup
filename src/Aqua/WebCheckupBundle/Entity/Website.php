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
  private $mobileReady = FALSE;
  private $mobileScore = 0;

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

  public function isMobileReady()
  {
      return $this->mobileReady;
  }
  public function isMobileReadyLiteral()
  {
      return ($this->mobileReady) ? 'Yes' : 'No';
  }

  public function setMobileReady($mobileReady)
  {
      $this->mobileReady = $mobileReady;
  }

  public function getMobileScore()
  {
      return $this->mobileScore;
  }

  public function setMobileScore($mobileScore)
  {
      $this->mobileScore += $mobileScore;
  }
}
