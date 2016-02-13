<?php
/**
 * Website class contains all informatios about a website and his scores.
 *
 * Copyright (C) 2015  Roberto Peruzzo <roberto.peruzzo@studioaqua.it>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
     * The max value of an integer is 2147483647, this means
     * a boundary of 2047,99999904632568 MB. I think there aren't websites
     * which can reach this limit.
     *
     * @ORM\Column(type="integer", name="mobile_response_bytes")
     */
    protected $mobileResponseBytes = 0;

    /**
     * Total energy consumed for loading the setted URL.
     *
     * @ORM\Column(type="float", name="energy_joules")
     */
    protected $energyJoules = 0;

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
    * Get mobileReady.
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
     * Get websiteId.
     *
     * @return integer
     */
    public function getWebsiteId()
    {
        return $this->websiteId;
    }

    /**
     * Set created.
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
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set mobilePageSpeed.
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
     * Get mobilePageSpeed.
     *
     * @return integer
     */
    public function getMobilePageSpeed()
    {
        return $this->mobilePageSpeed;
    }

    /**
     * Set mobileResponseBytes.
     *
     * @param integer $mobileResponseBytes
     * @return Website
     */
    public function setMobileResponseBytes($mobileResponseBytes)
    {
        $this->mobileResponseBytes = $mobileResponseBytes;

        return $this;
    }

    /**
     * Get mobileResponseBytes.
     *
     * @return integer
     */
    public function getMobileResponseBytes()
    {
        return $this->mobileResponseBytes;
    }

    /**
     * Get mobileResponseBytes human readable value.
     *
     * @return integer
     */
    public function getMobileResponseBytesHR()
    {
        return number_format(($this->mobileResponseBytes / 1048576), 2); // 1024*1024
    }

    /**
     * Set energyJoules
     *
     * @param float $energyJoules
     * @return Website
     */
    public function setEnergyJoules($energyJoules)
    {
        $this->energyJoules = $energyJoules;

        return $this;
    }

    /**
     * Get energyJoules
     *
     * @return float
     */
    public function getEnergyJoules()
    {
        return $this->energyJoules;
    }

    /**
     * Convert joules in Watt per Seconds. For this measure we choose a
     * 25W sample. We answer to this question: "How many seconds a 25W light
     * stays on?".
     *
     * @return float
     *         seconds for a 25W light on.
     */
    public function convertJoulesIn25W()
    {
        return $this->energyJoules / 25;
    }

    /**
     * Returns the human readable time.
     *
     * @return string.
     */
    protected function convertSecondsHumanReadable($seconds)
    {
        if ($seconds < 60)
        {
            return number_format($seconds, 2) . ' seconds';
        }
        elseif ($seconds >= 60
            && $seconds < 3600)
        {
            return number_format($seconds / 60, 2) . ' minutes';
        }
        elseif ($seconds >= 3600
            && $seconds < 86400)
        {
            return number_format($seconds / 3600, 2) . ' hours';
        }
        else
        {
            return number_format($seconds / 3600, 2) . ' days';
        }
    }

    /**
     * Computes the time amount based on website visitors estimation.
     *
     * @param  [int $visitors_number
     *         estimated visitors number.
     *
     * @return float
     */
    protected function getTimeForVisitors($visitors_number)
    {
        return $this->convertJoulesIn25W() * $visitors_number;
    }

    public function getTimeFor10Visitors()
    {
        return $this->convertSecondsHumanReadable($this->getTimeForVisitors(10));
    }

    public function getTimeFor500Visitors()
    {
        return $this->convertSecondsHumanReadable($this->getTimeForVisitors(500));
    }

    public function getTimeFor10000Visitors()
    {
        return $this->convertSecondsHumanReadable($this->getTimeForVisitors(10000));
    }


}
