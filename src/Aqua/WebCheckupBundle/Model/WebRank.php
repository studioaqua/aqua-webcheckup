<?php
/**
 * WebRank class get the Google PageSpeed Insight and Google Mobile Friendly
 * score of your website.
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

namespace Aqua\WebCheckupBundle\Model;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;

define('WATT', 25);
define('ENERGY_CONSUMED_PER_BIT_LOW', 7.5E-5);
define('ENERGY_CONSUMED_PER_BIT_HIGH', 4E-4);

class WebRank
{
  // Logger
  private $logger;

  /**
   * @var string
   */
  private $gateway = 'https://www.googleapis.com/pagespeedonline/v3beta1';
  /**
   * @var string
   */
  private $googleApiKey = 'AIzaSyA-6Q41S5-Rai9nV4vCpxr4WvBG7TEBGJ4';

  // Google PageSpeed API Client
  private $pageSpeed;

  public function __construct(LoggerInterface $logger)
  {
    // Init Logger
    $this->logger = $logger;

    // Init Google API Client
    $this->pageSpeed = new \PageSpeed\Insights\Service();
    /*
    $this->google_client->setApplicationName("Web Checkup");
    $this->google_client->setDeveloperKey("AIzaSyA-6Q41S5-Rai9nV4vCpxr4WvBG7TEBGJ4");
    */

    $this->logger->debug('Init WebRank');
  }

  /**
   * Checks if a website is mobile ready.
   * Use Google PageSpeed mobile ready API.
   *
   * @param string $url
   * @param string $apiKey
   * @return mixed
   */
  private function getMobileReadyResults($url, $locale = 'en_US', $strategy = 'mobile')
  {
    $client = new \Guzzle\Service\Client($this->gateway);

    /** @var $request \Guzzle\Http\Message\Request */
    $request = $client->get('mobileReady');
    $request->getQuery()
      ->set('key', $this->googleApiKey)
      ->set('url', $url)
      ->set('locale', $locale)
      ->set('screenshot', FALSE)
      ->set('strategy', $strategy);

    try
    {
      $response = $request->send();
      $this->logger->debug('Response: ' . var_export($response, TRUE));
      $response = $response->getBody();
      $response = json_decode($response, true);

      return $response;
    }
    catch (\Guzzle\Http\Exception\ClientErrorResponseException $e)
    {
      $response = $e->getResponse();
      $response = $response->getBody();
      $response = json_decode($response);

      throw new RuntimeException($response->error->message, $response->error->code);
    }
  }

  /**
   * Questa funzione dev'essere chiamata in modo sincrono, finché non è stato
   * completata la scansione della pagina per il ranking nessunaltro può
   * accedere a questa porzione di codice. Per fare questo viene utilizzato
   * un file di lock.
   */
  public function runCheckup(Website &$website)
  {

    /*
    Mobile friendly checkup.
     */
    $mobile_result = $this->getMobileReadyResults($website->getWebsite());

    //$mobile_result = $this->isMobileReady($website->getWebsite(), GOOGLE_API_KEY);
    //$this->logger->debug(var_export($mobile_result, TRUE));

    $website->setMobileReady($mobile_result['ruleGroups']['USABILITY']['pass']);
    $website->setMobileScore($mobile_result['ruleGroups']['USABILITY']['score']);

    /*
    Page Speed.
     */
    $pageSpeed_result = $this->pageSpeed->getResults(
      $website->getWebsite(), 'it_IT', 'mobile');
    $this->logger->debug(var_export($pageSpeed_result, TRUE));

    $website->setTitle($pageSpeed_result['title']);
    $website->setMobilePageSpeed($pageSpeed_result['ruleGroups']['SPEED']['score']);

    $totalBytes = (int) $pageSpeed_result['pageStats']['htmlResponseBytes'];
    $totalBytes += (int) isset($pageSpeed_result['pageStats']['textResponseBytes'])
      ? $pageSpeed_result['pageStats']['textResponseBytes']
      : 0;
    $totalBytes += (int) isset($pageSpeed_result['pageStats']['cssResponseBytes'])
      ? $pageSpeed_result['pageStats']['cssResponseBytes']
      : 0;
    $totalBytes += (int) isset($pageSpeed_result['pageStats']['imageResponseBytes'])
      ? $pageSpeed_result['pageStats']['imageResponseBytes']
      : 0;
    $totalBytes += (int) isset($pageSpeed_result['pageStats']['javascriptResponseBytes'])
      ? $pageSpeed_result['pageStats']['javascriptResponseBytes']
      : 0;
    $totalBytes += (int) isset($pageSpeed_result['pageStats']['flashResponseBytes'])
      ? $pageSpeed_result['pageStats']['flashResponseBytes']
      : 0;
    $totalBytes += (int) isset($pageSpeed_result['pageStats']['otherResponseBytes'])
      ? $pageSpeed_result['pageStats']['otherResponseBytes']
      : 0;

    $website->setMobileResponseBytes($totalBytes);
    $this->logger->debug('Web Checkup result => ' . var_export($website, TRUE));

    $totalBits = $totalBytes * 8;
    $website->setEnergyJoules($totalBits * ENERGY_CONSUMED_PER_BIT_LOW);
    $this->logger->debug('Total energy consumed per bit of data (J): ' . $website->getEnergyJoules());

  }

}

?>
