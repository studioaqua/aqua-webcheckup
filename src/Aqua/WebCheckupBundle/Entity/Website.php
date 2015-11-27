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
  private $mobileFriendly = FALSE;
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

  public function isMobileFriendly()
  {
      return $this->mobileFriendly;
  }
  public function isMobileFriendlyLiteral()
  {
      return ($this->mobileFriendly) ? 'Yes' : 'No';
  }

  public function setMobileFriendly($mobileFriendly)
  {
      $this->mobileFriendly = $mobileFriendly;
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
