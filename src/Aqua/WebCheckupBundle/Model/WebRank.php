<?php

namespace Aqua\WebCheckupBundle\Model;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;

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
    $website->setTitle($pageSpeed_result['title']);
    $website->setMobilePageSpeed($pageSpeed_result['ruleGroups']['SPEED']['score']);

    $website->setMobileResponseBytes = (int) $pageSpeed_result['pageStats']['htmlResponseBytes'];
    $website->setMobileResponseBytes += (int) isset($pageSpeed_result['pageStats']['textResponseBytes'])
      ? $pageSpeed_result['pageStats']['textResponseBytes']
      : 0;
    $website->setMobileResponseBytes += (int) isset($pageSpeed_result['pageStats']['cssResponseBytes'])
      ? $pageSpeed_result['pageStats']['cssResponseBytes']
      : 0;
    $website->setMobileResponseBytes += (int) isset($pageSpeed_result['pageStats']['imageResponseBytes'])
      ? $pageSpeed_result['pageStats']['imageResponseBytes']
      : 0;
    $website->setMobileResponseBytes += (int) isset($pageSpeed_result['pageStats']['javascriptResponseBytes'])
      ? $pageSpeed_result['pageStats']['javascriptResponseBytes']
      : 0;
    $website->setMobileResponseBytes += (int) isset($pageSpeed_result['pageStats']['flashResponseBytes'])
      ? $pageSpeed_result['pageStats']['flashResponseBytes']
      : 0;
    $website->setMobileResponseBytes += (int) isset($pageSpeed_result['pageStats']['otherResponseBytes'])
      ? $pageSpeed_result['pageStats']['otherResponseBytes']
      : 0;

    $this->logger->debug('Web Checkup result => ' . var_export($website, TRUE));

  }

}

?>
