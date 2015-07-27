<?php

namespace Aqua\WebCheckupBundle\Model;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;
use Buzz\Browser;
//use Monolog\Processor\PsrLogMessageProcessor;

class WebRank
{
  // Logger
  private $logger;

  // Browser
  private $browser;

  // Lock filename
  private $lock_filename = '';
  private $lock_filename_respo = '';

  // Regular expression
  private $rexp_title = '/<title>(.*?)<\/title>/';
  private $rexp_flash_calls = "/(\.swf|swfobject.js|swfobject.)/";
  private $rexp_css_media = "/\@media[\w\s\(-]+:[\s]+([0-9]*)px\)[\s]*\{([\s\w\{\}#-:;]*)\}/";
  private $rexp_phone = "/(Phone|Tel|Telefono)/";

  // Excludes these js libraries from responsive checkup, because
  // we can find a @media query string inside the library's css but
  // the whole website is not responsive.
  private $exclude_css_libraries = array(
    'jquery.fancybox',
    'fonts.googleapis',
  );

  public function __construct(LoggerInterface $logger, Browser $browser)
  {
    // Init Logger
    $this->logger = $logger;

    // Init browser
    $this->browser = $browser;

    // Init lock filename
    $this->lock_filename = dirname(__FILE__) . '/.lock';
    $this->lock_filename_respo = dirname(__FILE__) . '/.lock.respo';

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
  private function has_flash($html_source)
  {
    /**
     * @todo migliorare l'accuratezza nell'identificare un sito in falsh
     */

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

  /**
   * This function checks if a given html code is responsive.
   * To do this, it verifies if the CSS files linked in this code contains
   * the media query directive @media. If it has, it will be responsive.
   *
   * @param  string  $html_source
   * @return boolean
   */
  private function is_responsive($html_source)
  {
    // Init output variable
    $output = FALSE;

    // Create the lock file
    $fh = fopen($this->lock_filename_respo, 'w');

    if (empty($html_source))
    {
      $this->logger->warn('[is_responsive] The HTML is empty');
    }
    else
    {
      $crawler = new Crawler();
      $crawler->addContent($html_source);

      $is_responsive_flag = $crawler->filter('link')->each(
        function (Crawler $node, $i)
        {
          $href = $node->attr('href');

          if (($node->attr('type') == 'text/css')
            && (!$this->strposa($href, $this->exclude_css_libraries)))
          {
            $response = $this->browser->get($href);
            $css_source = $response->getContent();

            preg_match_all($this->rexp_css_media, $css_source, $match);

            $this->logger->info('[is_responsive] CSS file {href} processed.',
              array('href' => $href));

            if (!empty($match[0]))
            {
              // When we find a "@media" directive, we will check the presence
              // of max-/min-width statements for mobile or tablet devices
              // (i.e. width < 768px).
              // If we find we will break the "for-cycle", because we don't
              // need to go forward with the responsive check
              // for this html.
              $break_point_array = $match[1];
              foreach ($break_point_array as $key => $value)
              {
                // Remove trailer } and all beginning/trailing white spaces.
                $css_rules = trim(rtrim($match[2][$key], "}"));

                if ((intval($value) < 768)
                  && (!empty($css_rules)))
                {
                  $this->logger->info('[is_responsive] YES');
                  return TRUE;
                }
              }
            }
          }
          else
          {
            $this->logger->debug('[is_responsive] CSS file {href} excluded.',
              array('href' => $href));
          }
        }
      );

      if (in_array(TRUE, $is_responsive_flag))
      {
        $output = TRUE;
      }
    }


    // Delete lock file
    fclose($fh);
    unlink($this->lock_filename_respo);

    return $output;
  }


  /**
   * Questa funzione dev'essere chiamata in modo sincrono, finché non è stato
   * completata la scansione della pagina per il ranking nessunaltro può
   * accedere a questa porzione di codice. Per fare questo viene utilizzato
   * un file di lock.
   */
  public function runCheckup(Website &$website)
  {
    // Create the lock file
    $fh = fopen($this->lock_filename, 'w');

    // Is a flash website?
    $website->setFlash(
      $this->has_flash($website->getHtmlSource()));
    if ($website->isFlash())
    {
      $website->setRank(-5);
    }
    else
    {
      $website->setRank(1);
    }

    // Wait until lock file exists
    while( file_exists($this->lock_filename_respo) )
    {
      //$this->logger->debug('Wait responsive');
      //sleep(1);
      echo ':';
    }

    // Is a responsive website?
    $website->setResponsive(
      $this->is_responsive($website->getHtmlSource()));
    if ($website->isResponsive())
    {
      $website->setRank(2);
    }
    else
    {
      $website->setRank(-3);
    }

    // Delete lock file
    fclose($fh);
    unlink($this->lock_filename);

  }


  protected function get_phone($html_source)
  {

  }


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

}

?>
