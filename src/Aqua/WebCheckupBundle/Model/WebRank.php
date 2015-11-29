<?php

namespace Aqua\WebCheckupBundle\Model;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;
use Buzz\Browser;
use Buzz\Exception\RequestException;

define('GOOGLE_MOBILE_CHECK_URL',
  'https://www.google.com/webmasters/tools/mobile-friendly/?hl=it&url=');

class WebRank
{
  // Logger
  private $logger;

  // Google PageSpeed API Client
  //private $page_speed;

  // Doctrine EntityManager
  //private $em;



  // Browser
  //private $browser;

  // Lock filename
  //private $lock_filename = '';
  //private $lock_filename_respo = '';

  // Regular expression
  //private $rexp_title = '/<title>(.*?)<\/title>/';
  //private $rexp_flash_calls = "/(\.swf|swfobject.js|swfobject.)/";
  //private $rexp_css_media = "/\@media[\w\s\(-]+:[\s]+([0-9]*)px\)[\s]*\{([\s\w\{\}#-:;]*)\}/";
  //private $rexp_css_media = "/@media.+?[\)^]/";
  //private $rexp_phone = "/(Phone|Tel|Telefono)/";

  // Excludes these js libraries from responsive checkup, because
  // we can find a @media query string inside the library's css but
  // the whole website is not responsive.
  //private $exclude_css_libraries = array(
  //  'jquery.fancybox',
  //  'fonts.googleapis',
  //);

  public function __construct(LoggerInterface $logger)
  {
    // Init Logger
    $this->logger = $logger;

    // Init Google API Client
    //$this->page_speed = new \PageSpeed\Insights\Service();
    /*
    $this->google_client->setApplicationName("Web Checkup");
    $this->google_client->setDeveloperKey("AIzaSyA-6Q41S5-Rai9nV4vCpxr4WvBG7TEBGJ4");
    */

    // Init Doctrine EntityManager
    //$this->em = $em;

    // Init browser
    //$this->browser = $browser;

    // Init lock filename
    //$this->lock_filename = dirname(__FILE__) . '/.lock';
    //$this->lock_filename_respo = dirname(__FILE__) . '/.lock.respo';

    $this->logger->debug('Init WebRank');
  }

  /**
   * This function checks if a given html code contains some flash elements.
   * To do this, it parses code and verifies if there is a string matching
   * with these following words:
   *   - <object
   *   - <embed
   *   - .swf
   *   - swfobject.js
   *   - swfobject.
   * If it has, it will contain flash element.
   *
   * @param  string  $html_source
   * @return boolean
   */
/*
  private function has_flash($html_source)
  {
    preg_match_all($this->rexp_flash_calls, $html_source, $match);

    if (empty($match[1]))
    {
      $this->logger->debug('[has_flash] NO');
      return FALSE;
    }
    else
    {
      $this->logger->debug('[has_flash] YES');
      return TRUE;
    }
  }
*/

  /**
   * [is_mobile_friendly description]
   * @param  [type]  $url [description]
   * @return boolean      [description]
   */
/*
  private function is_mobile_friendly($url)
  {
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE)
    {
      $this->logger->error(
        sprintf('%s is not a valid URL.', $url)
      );
      return FALSE;
    }
    else
    {
      $this->logger->debug(
          sprintf('Checking %s',  $url)
      );

      try {

      }
      catch(RequestException $e)
      {
        $this->logger->error($e->getMessage());
        return FALSE;
      }
    }

  }
*/

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
          CURLOPT_URL => 'https://www.googleapis.com/pagespeedonline/v3beta1/mobileReady?key='.$apiKey.'&url='.$url.'&strategy=mobile',
      ));
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;
  }

  /**
   * Questa funzione dev'essere chiamata in modo sincrono, finché non è stato
   * completata la scansione della pagina per il ranking nessunaltro può
   * accedere a questa porzione di codice. Per fare questo viene utilizzato
   * un file di lock.
   */
  public function runCheckup(Website &$website)
  {

    // Mobile friendly checkup.
    $mobile_result = json_decode(
      $this->isMobileReady(
        $website->getWebsite(), 'AIzaSyA-6Q41S5-Rai9nV4vCpxr4WvBG7TEBGJ4'), true);

    //$this->logger->debug('Page speed => ' . var_export($mobile_result['ruleGroups']['USABILITY'], TRUE));

    $website->setMobileReady($mobile_result['ruleGroups']['USABILITY']['pass']);
    $website->setMobileScore($mobile_result['ruleGroups']['USABILITY']['score']);

    $this->logger->debug('Web Checkup result => ' . var_export($website, TRUE));

  }

/*
  private function strposa($haystack, $needle, $offset=0)
  {
    if(!is_array($needle))
    {
      $needle = array($needle);
    }

    foreach($needle as $query)
    {
      if(strpos($haystack, $query, $offset) !== false)
      {
        // stop on first true result
        return true;
      }
    }
    return false;
  }
*/

}

?>
