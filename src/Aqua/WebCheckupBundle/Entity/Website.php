<?php
/**
 *
 */
namespace Aqua\WebCheckupBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Website.
 *
 * @ORM\Entity
 * @ORM\Table(name="website")
 */
class Website
{

  /**
   * @ORM\Column(type="integer", name="wid")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $websiteId;

  /**
   * @ORM\Column(length=255)
   * @Assert\Url()
   */
  protected $website;

  /**
   * @ORM\Column(type="boolean", name="mobile_ready")
   */

  protected $mobileReady = FALSE;

  /**
   * @ORM\Column(type="integer", name="mobile_score")
   */
  protected $mobileScore = 0;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $created;


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
