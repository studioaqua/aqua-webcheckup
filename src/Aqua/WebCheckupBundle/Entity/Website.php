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
     */
    protected $title;

    /**
     * @ORM\Column(length=255)
     * @Assert\Url()
     */
    protected $website = 'http://';

    /**
     * @ORM\Column(type="boolean", name="mobile_ready")
     */
    protected $mobileReady = FALSE;

    /**
     * @ORM\Column(type="integer", name="mobile_score")
     */
    protected $mobileScore = 0;

    /**
     * @ORM\Column(type="integer", name="mobile_page_speed")
     */
    protected $mobilePageSpeed = 0;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @ORM\Version
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

    /**
    * Get mobileReady
    *
    * @return boolean
    */
    public function getMobileReady()
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

    /**
     * Get websiteId
     *
     * @return integer
     */
    public function getWebsiteId()
    {
        return $this->websiteId;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Website
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Website
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set mobilePageSpeed
     *
     * @param integer $mobilePageSpeed
     * @return Website
     */
    public function setMobilePageSpeed($mobilePageSpeed)
    {
        $this->mobilePageSpeed = $mobilePageSpeed;

        return $this;
    }

    /**
     * Get mobilePageSpeed
     *
     * @return integer
     */
    public function getMobilePageSpeed()
    {
        return $this->mobilePageSpeed;
    }
}
