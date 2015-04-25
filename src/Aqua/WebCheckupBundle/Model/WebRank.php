<?php

namespace Aqua\WebCheckupBundle\Model;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;
use Monolog\Processor\PsrLogMessageProcessor;

class WebRank
{
  // Logger
  protected $logger;

  // Buzz Browser
  protected $browser;

  // Website
  // Output values, rank = 0 means no checkup has been performed.
  protected $website;

  // Lock filename
  public $lock_filename = '';
  public $lock_filename_respo = '';
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

  public function __construct(Website $website, $buzz, LoggerInterface $logger)
  {
    // Init Logger
    $this->logger = $logger;

    // Init Browser
    $this->browser = $buzz;

    // Init Website
    $this->website = $website;

    // Init page url
    //$this->checkout['name'] = $website_name;
    //$this->checkout['url'] = $url;

    $this->logger->info('######## WEB CHECKUP');

    // Init lock filename
    //$this->lock_filename = dirname(__FILE__) . '/.lock';
    //$this->lock_filename_respo = dirname(__FILE__) . '/.lock.respo';

    $this->logger->debug('Init url {url} and lock file {file}', array(
      'url' => $this->website->getWebsite(),
      'file' => $this->lock_filename));
  }

  /**
   * Returns the html page content
   */
  protected function get_page($url = '')
  {
    if (empty($url))
    {
      $url = $this->checkout['url'];
    }

    $snoopy = new Snoopy;
    $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
    $snoopy->_httpmethod = "GET"; // Metodo POST
    $snoopy->referer = "";
    $snoopy->fetch($url);
    $pagedata = $snoopy->results;

    return $pagedata;
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
    $this->logger->debug('[has_flash] Rexp match -> ' . var_export($match[1], TRUE));

    if (empty($match[1]))
    {
      return FALSE;
    }
    else
    {
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
    // Create the lock file
    $fh = fopen($this->lock_filename_respo, 'w');

    $is_responsive_flag = FALSE;

    // Create a DOM object from a string
    $html = str_get_html($html_source);

    if (empty($html))
    {
      $this->logger->warn('[is_responsive] The HTML is empty');
    }
    else
    {
      // Find all link tags
      foreach($html->find('link') as $element)
      {
        if ($element->type == 'text/css')
        {
          if (!$this->strposa($element->href, $this->exclude_css_libraries))
          {
            $css = $this->get_page($this->checkout['url'] . '/' . $element->href);

            preg_match_all($this->rexp_css_media, $css, $match);
            $this->logger->debug('[is_responsive] Rexp match -> ' . var_export($match, TRUE));

            $this->logger->info('[is_responsive] CSS file ' . $element->href . ' processed.');

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
                $this->logger->debug('[is_responsive] css_rules -> ' . var_export($css_rules, TRUE));
                if ((intval($value) < 768)
                  && (!empty($css_rules)))
                {
                  $this->logger->info('[is_responsive]  ' . $match[0][$key]);
                  $is_responsive_flag = TRUE;
                  // Exit the both foreach
                  break 2;
                }
              }
            }
          }
          else
          {
            $this->logger->info('[is_responsive] CSS file ' . $element->href . ' excluded.');
          }
        }
      }
    }

    // Delete lock file
    fclose($fh);
    unlink($this->lock_filename_respo);

    return $is_responsive_flag;
  }


  /**
   * Questa funzione dev'essere chiamata in modo sincrono, finché non è stato
   * completata la scansione della pagina per il ranking nessunaltro può
   * accedere a questa porzione di codice. Per fare questo viene utilizzato
   * un file di lock.
   *
   * @return array
   */
  public function get_rank()
  {
    // Create the lock file
    $fh = fopen($this->lock_filename, 'w');

    $html = $this->get_page();

    // Is a flash website?

    if ($this->has_flash($html))
    {
      $this->checkout['rank'] -= 5;
      $this->checkout['flash'] = 'YES';
    }
    else
    {
      $this->checkout['rank'] += 1;
    }

    // Wait until lock file exists
    while( file_exists($this->lock_filename_respo) )
    {
      //$this->logger->debug('Wait responsive');
      //sleep(1);
      echo ':';
    }

    // Is a responsive website?
    if ($this->is_responsive($html))
    {
      $this->checkout['rank'] += 2;
      $this->checkout['responsive'] = 'YES';
    }
    else
    {
      $this->checkout['rank'] -= 3;
    }



    // Delete lock file
    fclose($fh);
    unlink($this->lock_filename);

    return $this->checkout;
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
      if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
    }
    return false;
  }

}

?>
