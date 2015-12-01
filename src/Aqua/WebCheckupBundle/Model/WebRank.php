<?php

namespace Aqua\WebCheckupBundle\Model;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;
use Buzz\Browser;
use Buzz\Exception\RequestException;

define('GOOGLE_MOBILE_CHECK_URL',
  'https://www.googleapis.com/pagespeedonline/v3beta1/mobileReady?key=%s&url=%s&strategy=mobile');
define('GOOGLE_API_KEY', 'AIzaSyA-6Q41S5-Rai9nV4vCpxr4WvBG7TEBGJ4');

class WebRank
{
  // Logger
  private $logger;

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
  private function isMobileReady($url, $apiKey)
  {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => sprintf(GOOGLE_MOBILE_CHECK_URL, $apiKey, $url),
      )
    );
    $resp = curl_exec($curl);
    curl_close($curl);

    return json_decode($resp, TRUE);
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
    $mobile_result = $this->isMobileReady(
      $website->getWebsite(), GOOGLE_API_KEY);

    //$mobile_result = $this->isMobileReady($website->getWebsite(), GOOGLE_API_KEY);
    //$this->logger->debug(var_export($mobile_result, TRUE));

    $website->setMobileReady($mobile_result['ruleGroups']['USABILITY']['pass']);
    $website->setMobileScore($mobile_result['ruleGroups']['USABILITY']['score']);

    /*
    Page Speed.
     */
    $pageSpeed_result = $this->pageSpeed->getResults(
      $website->getWebsite(), 'it_IT', 'mobile');
    $website->setTitle($pageSpeed_result['title']);
    $website->setMobilePageSpeed($pageSpeed_result['ruleGroups']['SPEED']['score']);

    $this->logger->debug('Web Checkup result => ' . var_export($website, TRUE));

  }

}

?>
