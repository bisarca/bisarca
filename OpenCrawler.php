<?php

/**
 * cURL referer for the extraction of content
 * @var string
 */
define('OPENCRAWLER_REFERER', 'https://github.com/EmanueleMinotto/OpenCrawler');
/**
 * Agent (used in the User Agent and the control of robots.txt)
 * @var string
 */
define('OPENCRAWLER_AGENT', 'OpenCrawler');
/**
 * Compatibility with the User-Agent browser without losing the special crawler
 * @var string
 */
define('OPENCRAWLER_USERAGENT', 'Mozilla/5.0 (compatible; ' . OPENCRAWLER_AGENT . '; +' . OPENCRAWLER_REFERER . '; Trident/4.0)');
/**
 * Given the structure of the OpenCrawler history is impossible to determine the exact date of the visit of a given page, 
 * so we need for the Re-visit policy set a minimum size limit for the array
 * @link http://en.wikipedia.org/wiki/Web_crawler#Re-visit_policy
 * @var int
 */
define('OPENCRAWLER_HISTORY', 15000);
/**
 * Cookies file & directory
 * @var string
 */
define('OPENCRAWLER_COOKIES', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'OpenCrawlerCookies.txt');

/**
 * OpenCrawler
 * 
 * Class for spidering the network, in a standalone file
 * 
 * Examinations are conducted in width, 
 * extracting all possible information including: content, dom (DOMDocument), 
 * headers, robots.txt, download speeds and data-satellite...
 * @link http://en.wikipedia.org/wiki/Breadth-first_search
 * @link https://github.com/EmanueleMinotto/OpenCrawler
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @author Emanuele Minotto
 */
class OpenCrawler
{
    /**
     * Global variable of this class
     * Contains information regarding the current URL
     * @var array
     */
    public $handler = array();
    /**
     * Global variable that contains the secondary information
     * Contains information about the URLs visited on the duration of the instance
     * @var array
     */
    protected $bin = array();

    /**
     * Class constructor
     */
    function __construct()
    {
        $this -> bin['history'] = array();
        $this -> handler['a'] = array();
    }

    /**
     * Main function of the class, parses and extracts the link to follow
     * @param $url URL of the page to visit
     */
    public function loadUrl($url)
    {
        if (trim($url) == '')
        {
            return false;
        }
        
        $url = !preg_match('/^([a-z]+):\/\/(.*)/', $url) ? 'http://' . str_replace('://', null, $url) : $url;
        $url .= !@parse_url($url, PHP_URL_PATH) ? '/' : null;
        
        /**
         * Redirect via HTTP Location
         */
        $temp = $this -> parseHeaders($url);
        $counterHeaders = 0;
        while ($counterHeaders < 10 && ((isset($temp['Location']) && $temp['Location'] != $url) || (isset($temp['Content-Location']) && $temp['Content-Location'] != $url)))
        {
            if (!array_search($url, $this -> bin['history']))
            {
                $this -> pushLink($url, true);
                array_values(array_unique($this -> bin['history']));
            }
            
            if (isset($temp['Location']))
            {
                if (is_array($temp['Location']))
                {
                    $temp['Location'] = $temp['Location'][sizeof($temp['Location']) - 1];
                }
                $FullUri = $this -> absoluteUrl($url, $temp['Location']);
                
            }
            else
            {
                break;
            }
            
            $url = $FullUri;
            $temp = $this -> parseHeaders($url);
        }
        
        if ($this -> isValid($url))
        {
            $this -> pushLink($url, true);
        }
        
        $this -> handler['headers'] = $temp;
        unset($temp);
        
        if (preg_match('/#!([^#]+)$/', $url))
        {
            $this -> handler['url'] = $url;
            $url = $this -> ajaxUrlTransform($url);
        }
        else
        {
            $this -> handler['url'] =& $url;
        }
        
        if ($key = array_search($url, $this -> bin['history']))
        {
            for ($c = $key - 1; ($c > 0 && $c > $key - 6); $c--)
            {
                if ($this -> bin['history'][$c] == $url)
                {
                    return $this -> loadNext();
                }
            }
        }
        
        $temp =& $this -> handler['headers']['Content-Type'];
        if (is_array($temp))
        {
            $temp = $temp[sizeof($temp) - 1];
        }
        
        if (!isset($temp) || !preg_match('/(x|ht)ml/', $temp))
        {
            return false;
        }
        
        /**
         * Robot access control
         */
        $this -> handler['robots'] = $this -> parseRobots($url);
        if (!isset($this -> handler['sitemaps']))
        {
            $this -> loadSitemaps($this -> absoluteUrl($url, 'sitemap.xml'));
        }
        
        if (!$this -> isValid($url, true))
        {
            return false;
        }
        
        /**
         * Extraction of Contents
         */
        $this -> handler['dom'] = new DOMDocument;
        @$this -> handler['dom'] -> loadHTML($this -> loadContent($url));
        
        $this -> handler['a'] = array();
        
        if ($metas = $this -> handler['dom'] -> getElementsByTagName('meta'))
        {
            for ($c = 0; $c < $metas -> length; $c++)
            {
                $meta = $metas -> item($c);
                $attrs =& $meta -> attributes;
                
                if (isset($attrs -> getNamedItem("http-equiv") -> nodeValue) && strtolower($attrs -> getNamedItem("http-equiv") -> nodeValue) === "refresh")
                {
                    $metaContent = strtolower($attrs -> getNamedItem("content") -> nodeValue);
                    $newPath = preg_replace('/(.*)url=(.*)$/i', "$2", $metaContent);
                    $newUrl = $this -> absoluteUrl($url, $newPath);
                    if (!is_numeric($newPath) && $newUrl != $url && array_key_exists('scheme', parse_url($newUrl)))
                    {
                        return $this -> loadUrl($newUrl);
                    }
                    
                }
                elseif (isset($attrs -> getNamedItem("name") -> nodeValue) && strtolower($attrs -> getNamedItem("name") -> nodeValue) === "robots")
                {
                    $metaContent = strtolower($attrs -> getNamedItem("content") -> nodeValue);
                    $metaRobots = explode(',', $metaContent);
                    foreach ($metaRobots as $k => $v)
                    {
                        $metaRobots[$k] = trim($v);
                    }
                    
                }
                elseif (strtolower($attrs -> getNamedItem("name") -> nodeValue) === "fragment")
                {
                    if ($attrs -> getNamedItem("content") -> nodeValue === '!')
                    {
                        parse_str(parse_url($url, PHP_URL_QUERY), $parameters);
                        if (!array_key_exists('_escaped_fragment_', $parameters))
                        {
                            $this -> loadAjaxLinks($this -> handler['url']);
                        }
                    }
                }
            }
        }
        
        if ($links = $this -> handler['dom'] -> getElementsByTagName('link'))
        {
            for ($c = 0; $c < $links -> length; $c++)
            {
                $link = $links -> item($c);
                $attrs =& $link -> attributes;
                
                if (strtolower($attrs -> getNamedItem("rel") -> nodeValue) === "canonical")
                {
                    $url = $this -> absoluteUrl($attrs -> getNamedItem("href") -> nodeValue);
                }
                elseif (strtolower($link -> attributes -> getNamedItem("rel") -> nodeValue) === "sitemap")
                {
                    $this -> loadSitemaps($this -> absoluteUrl($link -> attributes -> getNamedItem("href") -> nodeValue));
                }
                elseif (array_search(strtolower($attrs -> getNamedItem("rel") -> nodeValue), array(
                    'appendix', 'chapter', 'contents', 'copyright', 'glossary', 'help', 'index', 'license', 'next', 'prev', 'previous', 'section', 'start', 
                    'subsection', 'tag', 'toc', 'home', 'directory', 'bibliography', 'cite', 'archive', 'archives', 'external'
                    )))
                {
                    $this -> pushLink($this -> absoluteUrl($attrs -> getNamedItem("href") -> nodeValue));
                }
                
            }
        }
        
        if (!isset($metaRobots) || !array_search('nofollow', $metaRobots))
        {
            $this -> loadLinks($this -> handler['dom']);
        }
        
        if (isset($metaRobots) && array_search('noindex', $metaRobots))
        {
            return false;
        }
        
        $this -> bin['history'] = array_values(array_unique($this -> bin['history']));
        $this -> handler['a'] = array_values(array_unique($this -> handler['a']));
        
        return true;
    }

    /**
     * robots.txt parsing
     * Parse the robots.txt file in the root directory and returns an array with the path set out in an orderly array on the User-Agent, 
     * the User-Agent default is the asterisk '*'
     * @link http://www.conman.org/people/spc/robots2.html
     * @param string $url
     * @return array
     */
    public function parseRobots($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (isset($this -> bin['robots'][$host]))
        {
            return $this -> bin['robots'][$host];
        }
        
        $url = parse_url($url, PHP_URL_SCHEME) . '://' . $host . '/robots.txt';
        $robots = array();
        $UserAgent = '*';
        
        $this -> bin['robots'][$host] =& $robots;
        
        try
        {
            $rules = @file($url);
        }
        catch (Exception $Exception)
        {
            return array();
        }
        
        /**
         * @todo Controllo preg_match
         */
        for ($c = 0; $c < sizeof($rules); $c++)
        {
            $rule = trim(preg_replace('/(.*)?\#(.*)/', "$1", $rules[$c]));
            if ($rule == null)
            {
                continue;
            }
            elseif (preg_match('/^User-Agent:(.*)/i', $rule, $match))
            {
                $UserAgent = trim($match[1]);
            }
            elseif (preg_match('/^Disallow:(.*)/i', $rule, $match) && trim($match[1]) != null && preg_match('/^'.str_replace('*', '.*', $UserAgent).'$/i', OPENCRAWLER_AGENT))
            {
                $robots[$UserAgent][] = trim($match[1]);
            }
            elseif (preg_match('/^Sitemap:(.*)/i', $rule, $match) && array_key_exists('scheme', parse_url(trim($match[1]))))
            {
                $this -> loadSitemaps(trim($match[1]));
            }
            elseif (preg_match('/^Crawl\-delay:(.*)/i', $rule, $match) && trim($match[1]) != null && preg_match('/^'.str_replace('*', '.*', $UserAgent).'$/i', OPENCRAWLER_AGENT))
            {
                $robots[$UserAgent]['Crawl-delay'] = trim($match[1]);
            }
        }
        return $robots;
    }

    /**
     * Extraction of Headers, if you can not extract Headers returns an empty array
     * @param string $url
     * @return array
     */
    function parseHeaders($url)
    {
        $this -> bin['headers'][$url] =& $headers;
        
        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_FILETIME => true,
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => OPENCRAWLER_COOKIES,
            CURLOPT_COOKIEFILE => OPENCRAWLER_COOKIES,
            CURLOPT_REFERER => OPENCRAWLER_REFERER,
            CURLOPT_USERAGENT => OPENCRAWLER_USERAGENT,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_MAXREDIRS => 10
        );
        curl_setopt_array($curl, $options);
        $headers = curl_exec($curl);
        
        $headers = preg_split('/[\n|\r]/', $headers, 0, PREG_SPLIT_NO_EMPTY);
        foreach ($headers as $k => $v)
        {
            if (!preg_match('/^([a-z0-9\-]+):( +)?(.*)$/i', $v, $matches) || ctype_digit($matches[1]) || $k == 0)
            {
                $headers_new[$k] = $v;
            }
            else
            {
                $headers_new[trim($matches[1])] = $matches[3];
            }
        }
        $headers = $headers_new;
        
        curl_close($curl);
        return $headers;
    }
    
    /**
     * Loading the contents of the page with cURL
     * @link http://php.net/curl
     * @param string $url
     * @return string
     */
    public function loadContent($url)
    {
        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => OPENCRAWLER_COOKIES,
            CURLOPT_COOKIEFILE => OPENCRAWLER_COOKIES,
            CURLOPT_REFERER => OPENCRAWLER_REFERER,
            CURLOPT_USERAGENT => OPENCRAWLER_USERAGENT,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_MAXREDIRS => 10
        );
        curl_setopt_array($curl, $options);
        $content = curl_exec($curl);
        
        $this -> handler['info'] = curl_getinfo($curl);
        $this -> bin['info'][$url] =& $this -> handler['info'];
        
        curl_close($curl);
        unset($curl, $options);
        return $content;
    }

    /**
     * Loading the next link in this bin[history]
     */
    public function loadNext()
    {
        $history =& $this -> bin['history'];
        $history = array_values(array_unique($history));
        if (isset($this -> handler['a']))
        {
            $this -> handler['a'] = array_values(array_unique($this -> handler['a']));
        }
        
        if (sizeof($history) > OPENCRAWLER_HISTORY)
        {
            while (sizeof($history) > OPENCRAWLER_HISTORY)
            {
                $history = array_shift($history);
            }
            $history = array_values(array_unique($history));
        }
        
        $key = array_search($this -> handler['url'], $history);
        if (isset($history[$key + 1]))
        {
            if (parse_url($history[$key], PHP_URL_HOST) == parse_url($history[$key + 1], PHP_URL_HOST))
            {
                $domain = parse_url($history[$key], PHP_URL_HOST);
                foreach ($this -> bin['robots'][$domain] as $agent => $rules)
                {
                    if (isset($rules['Crawl-delay']))
                    {
                        $tempS = $rules['Crawl-delay'];
                    }
                }
                if (isset($this -> bin['robots'][$domain][OPENCRAWLER_AGENT]['Crawl-delay']))
                {
                    $tempS = $this -> bin['robots'][$domain][OPENCRAWLER_AGENT]['Crawl-delay'];
                }
                
                if (isset($tempS) && is_numeric($tempS))
                {
                    sleep($tempS);
                }
                
            }
            $this -> loadUrl($history[$key + 1]);
        }
        else
        {
            return false;
        }
    }

    /**
     * Extraction of anchor links via the DOMDocument object
     * @param mixed &$dom DOMDocument Object
     */
    public function loadLinks(&$dom)
    {
        $bases = $dom -> getElementsByTagName('base');
        $base = $this -> handler['url'];
        if ($bases -> length && $tmp_href =& $bases -> item(0) -> attributes -> getNamedItem("href") && $tmp_href -> nodeValue)
        {
            $base = $bases -> item(0) -> attributes -> getNamedItem("href") -> nodeValue;
        }
        
        foreach ($dom -> getElementsByTagName('a') as $a)
        {
            if (!isset($a -> attributes -> getNamedItem("href") -> nodeValue))
            {
                continue;
            }
            
            $aHref = $this -> absoluteUrl($base, $a -> attributes -> getNamedItem("href") -> nodeValue);
            $aHref = $this -> cleanUrl($aHref);
            
            
            if (!$this -> isValid($aHref))
            {
                continue;
            }
            
            if (isset($a -> attributes -> getNamedItem("rel") -> nodeValue))
            {
                if (array_search('nofollow', explode(' ', $a -> attributes -> getNamedItem("rel") -> nodeValue)))
                {
                    continue;
                }
            }
            
            $this -> pushLink($aHref);
        }
        $this -> bin['history'] = array_values(array_unique($this -> bin['history']));
        $this -> handler['a'] = array_values(array_unique($this -> handler['a']));
    }

    /**
     * Check if the robot can visit a URL
     * @param string $url Address to be checked
     * @return bool
     */
    public function crawlerAccess($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $path = preg_replace('/^([a-z]+):\/\/([^\/]+)(.*)/', "$3", $url);
        if (isset($this -> bin['robots'][$domain]))
        {
            $robots_txt =& $this -> bin['robots'][$domain];
            foreach ($robots_txt as $agent => $rules)
            {
                if (trim($agent) == '')
                {
                    continue;
                }
                if (preg_match('/^'.str_replace('*', '.*', $agent).'$/i', OPENCRAWLER_AGENT))
                {
                    foreach ($robots_txt[$agent] as $k => $v)
                    {
                        if (is_string($v) && !is_numeric($v) && !is_bool($v))
                        {
                            $line = str_replace('/', '\/', str_replace('\*', '.+', quotemeta($v)));
                            if (preg_match('/^' . $line . '/', $path))
                            {
                                return false;
                            }
                        }
                    }
                    break;
                }
            }
        }
        return true;
    }
    
    /**
     * 
     * Extract OpenCrawler visits history
     */
    public function getHistory()
    {
        return $this -> bin['history'];
    }
    
    /**
     * 
     * Clear OpenCrawler history
     */
    public function clearHistory()
    {
        $this -> bin['history'] = array();
        return $this;
    }
    
    /**
     * 
     * Prevent strange things
     * @param string $url
     */
    private function cleanUrl($url)
    {
        return trim(str_replace(array("\n", "\r", "\t"), null, strip_tags($url)));
    }
    
    /**
     * 
     * Check if an URL can be added to history and anchors arrays
     * @param string $url
     */
    protected function isValid($url, $syntaxOnly = false)
    {
        // Is this a valid URL?
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^https?:/i', $url))
        {
            return false;
        }
        // Is it Javascript?
        if (preg_match('/^(javascript\:)/i', $url))
        {
            return false;
        }
        // Can our crawler access to that URL?
        if (!$this -> crawlerAccess($url))
        {
            return false;
        }
        // Is this URL already in history or in current page anchors?
        if (!$syntaxOnly)
        {
            if (array_search($url, $this -> bin['history']) || array_search($url, $this -> handler['a']))
            {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Loading a single Link
     * @param string $url Link to push in the internal stack
     * @param bool $historyOnly
     */
    public function pushLink($url, $historyOnly = false)
    {
        $url = $this -> cleanUrl($url);
        
        if ($this -> isValid($url))
        {
            if (!$historyOnly)
            {
                $this -> handler['a'][] = $url;
            }
            $this -> bin['history'][] = $url;
        }
        return $this;
    }

    /**************************************************************
    These functions aim to implement a relative URL resolver
    according to the RFC 1808 specification.
    Copyright (C) 2007 David Doran (http://www.daviddoranmedia.com/)
    ****************************************************************/
    public function absoluteUrl($base, $path = null, $fragment = false)
    {
        if (!filter_var(trim($base), FILTER_VALIDATE_URL))
        {
            $base = $this -> handler['url'];
        }
        
        // forget case
        if (preg_match('/^:?\/\/(.*)$/', $path))
        {
            $path = preg_replace('/^:?\/\/(.*)$/', parse_url($base, PHP_URL_SCHEME).'://'."$1", $path);
            $path = !$fragment ? trim(preg_replace('/(.*)?\#(.*)/', "$1", $path)) : $path;
            return $path;
        }
        
        $path = trim($path);
        $base = trim($base);
        $base_parse = @parse_url($base);

        $this -> filloutParse( $base_parse );

        $GOTO_SEVEN = FALSE;

        //Output variables
        $u = array();
        $u['scheme'] = '';
        $u['host'] = '';
        $u['path'] = '';
        $u['query'] = '';
        $u['params'] = '';
        $u['fragment'] = '';

        /* 1: The base URL is established according to the rules of Section 3.
         *    If the base URL is the empty string (unknown), the embedded URL
         *    is interpreted as an absolute URL and we are done.
         */
        if ($base == '' || $base_parse == false)
        {
            if ($path == '')
            {
                return false;
            }
            else
            {
                return $path;
            }
        }

        /* 2: Both the base and embedded URLs are parsed into their component
              parts as described in Section 2.4.
              1: If the embedded URL is entirely empty, it inherits the entire
                  base URL (i.e., is set equal to the base URL) and we are done.
              2: If the embedded URL starts with a scheme name, it is
                  interpreted as an absolute URL and we are done.
              3: Otherwise, the embedded URL inherits the scheme of the base URL.
        */
        $parse_url = @parse_url($path);
        if ($parse_url == false)
        {
            if ($path == '#')
            {
                return $base;
            }
            else
            {
                return false;
            }
        }

        $this -> filloutParse($parse_url);

        if (!is_array($parse_url))
        {
            // # means the current url!
            if ($path == '#')
            {
                return $base;
            }
            else
            {
                return false;
            }
        }
        if ($path == '')
        {
            return $base;
        }
        if (isset($parse_url['scheme']) && trim($parse_url['scheme']) != '')
        {
            if (!isset($parse_url['path']) || $parse_url['path'] == '')
            {
                $path .= '/';
            }
            return $path;
        }

        //Set the scheme equal to the base scheme
        $u['scheme'] = $base_parse['scheme'];
        $u['query'] = $parse_url['query'];
        $u['fragment'] = $parse_url['fragment'];

        /* 3: If the embedded URL's <net_loc> is non-empty, we skip to Step 7.
              Otherwise, the embedded URL inherits the <net_loc> (if any) of the base URL.
         */
        if (isset($parse_url['host']) && trim($parse_url['host']) != '')
        {
            //SKIP TO SECTION SEVEN
            $u['host'] = $parse_url['host'];
            $GOTO_SEVEN = true;
        }
        else
        {
            $u['host'] = $base_parse['host'];
        }

        /* 4: If the embedded URL path is preceded by a slash "/",
              the path is not relative and we skip to Step 7.
         */
        if (!$GOTO_SEVEN)
        {
            if (isset($parse_url['path'][0]) && $parse_url['path'][0] == '/')
            {
                //SKIP TO SECTION SEVEN
                $u['path'] = $parse_url['path'];
                $GOTO_SEVEN = true;
            }
        }


        /* 5: If the embedded URL path is empty (and not preceded by a slash),
              then the embedded URL inherits the base URL path, and
              1: if the embedded URL's <params> is non-empty, we skip to step 7;
                  otherwise, it inherits the <params> of the base URL (if any) and
              2: if the embedded URL's <query> is non-empty, we skip to step 7;
                  otherwise, it inherits the <query> of the base URL (if any) and we skip to step 7.
        */
        if (!$GOTO_SEVEN)
        {
            if (!isset($parse_url['path']) || $parse_url['path'] == '')
            {
                $u['path'] = $base_parse['path'];
                if (isset($parse_url['query']) && $parse_url['query'] != '')
                {
                    $u['query'] = $parse_url['query'];
                    $GOTO_SEVEN = true;
                }
                else
                {
                    $u['query'] = $base_parse['query'];
                    $GOTO_SEVEN = true;
                }
            }
        }

        /* 6: The last segment of the base URL's path (anything following the rightmost
              slash "/", or the entire path if no slash is present) is removed and the
              embedded URL's path is appended in its place.
              The following operations are then applied, in order, to the new path:
         */
        if (!$GOTO_SEVEN)
        {
            $base_path_strlen = ((isset($base_parse['path']))?( strlen( $base_parse['path'] ) ):( 0 ));
            $proc_path = '';
            //$exit_for = true;
            for ($i = ($base_path_strlen-1); $i > 0; $i--)
            {
                if ($base_parse['path'][$i] == '/')
                {
                    $proc_path = substr($base_parse['path'], 0, $i);
                    break;
                }
            }
            $u['path'] = ((!isset($proc_path[0]) || $proc_path[0] != '/') ? '/' : '') . $proc_path . (($parse_url['path'][0] != '/') ? '/' : '') . $parse_url['path'];
            $path_parse_array = array();
            $path_parse = $this -> parseSegments($u['path']);
            $path_parse_len = count($path_parse);
            $path_parse_keys = array_keys($path_parse);
            for ($i = 0; $i < $path_parse_len; $i++)
            {
                $cur = $path_parse[$path_parse_keys[$i]];
                if ($cur == '..')
                {
                    if (isset($path_parse_keys[$i - 1]))
                    {
                        unset($path_parse[$path_parse_keys[$i]]);
                        unset($path_parse[$path_parse_keys[$i - 1]]);
                        $i = $i - 2;
                        $path_parse_len = count($path_parse);
                        $path_parse_keys = array_keys( $path_parse );
                    }
                    else
                    {
                        unset($path_parse[$path_parse_keys[$i]]);
                        $i = $i - 1;
                        $path_parse_len = count($path_parse);
                        $path_parse_keys = array_keys($path_parse);
                    }
                }
                elseif ($cur == '.')
                {
                    unset($path_parse[$path_parse_keys[$i]]);
                    $i = $i - 1;
                    $path_parse_len = count($path_parse);
                    $path_parse_keys = array_keys( $path_parse );
                }
            }
            if ($path_parse_len > 0)
            {
                $u['path'] = '/' . implode('/', $path_parse);
            }
            else
            {
                $u['path'] = '/';
            }
        }
        //////////////////////////////
        //** THIS IS NUMBER SEVEN!!
        //////////////////////////////
        $frag = (($u['fragment'] != '' && $fragment != true) ? '#'. $u['fragment'] : '');
        return ($finish_url = $u['scheme'] . '://' . $u['host'] . $u['path'] .($u['query'] != '' ? '?'. $u['query'] : '') . $frag);
    }
     
    private function filloutParse(&$parse_array)
    {
        if (!isset($parse_array['scheme']))
        {
            $parse_array['scheme'] = '';
        }
        if (!isset($parse_array['host']))
        {
            $parse_array['host'] = '';
        }
        if (!isset($parse_array['path']))
        {
            $parse_array['path'] = '';
        }
        if (!isset($parse_array['query']))
        {
            $parse_array['query'] = '';
        }
        if (!isset($parse_array['fragment']))
        {
            $parse_array['fragment'] = '';
        }
    }
    
    private function parseSegments($str_path)
    {
        $str_path = trim($str_path);
        $str_len = strlen($str_path);
        $str_array = array();
        $str_array[0] = '';
        $str_num = 0;
        
        for ($i = 0; $i < $str_len; $i++)
        {
            $chr = $str_path[$i];
            if ($chr != '/')
            {
                $str_array[$str_num] .= $chr;
                continue;
            }
            if ($chr == '/' && $i < ($str_len-1))
            {
                $str_num++;
                $str_array[$str_num] = '';
                if (isset($str_array[$str_num-1]) && $str_array[$str_num - 1] == '')
                {
                    unset( $str_array[$str_num-1] );
                }
                continue;
            }
            if ($chr == '/' && count($str_array) < 1)
            {
                continue;
            }
        }
        return $str_array;
    }
    
	/**
     * Sitemaps Parsing
     * @param string $url Sitemap URL to visit
     * @return bool
     */
    function loadSitemaps($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (isset($this -> bin['sitemaps'][$host]))
        {
            return $this -> bin['sitemaps'][$host];
        }
        
        /**
         * Limit max sitemaps
         */
        $truncateLimit = (ini_get('max_execution_time') != 0) ? (200000 / 30 * ini_get('max_execution_time')) : 1073741824;
        
        $smHeader = $this -> parseHeaders($url);
        
        $smContentType = $smHeader['Content-Type'];
        
        if (is_array($smContentType))
        {
            $smContentType = $smContentType[sizeof($smContentType) - 1];
        }
        
        if (preg_match('/application\/x\-gzip/', $smContentType))
        {
            $smContent = gzfile($url);
            if (preg_match('/^</', $smContent[0]) && preg_match('/>$/', $smContent[sizeof($smContent) - 1]))
            {
                $smContent = implode($smContent);
                if (strlen($smContent) > $truncateLimit)
                {
                    return false;
                }
                
                $dom = new DOMDocument;
                $dom -> loadXML($smContent);
                
                if ($dom -> getElementsByTagName('sitemapindex') -> length)
                {
                    if (ini_get('max_execution_time') != 0)
                    {
                        $this -> loadSitemaps($dom -> getElementsByTagName('loc') -> item(0) -> nodeValue);
                    }
                    else
                    {
                        foreach ($dom -> getElementsByTagName('loc') as $loc)
                        {
                            $this -> loadSitemaps($loc -> nodeValue);
                        }
                    }
                }
                elseif ($dom -> getElementsByTagName('urlset'))
                {
                    foreach ($dom -> getElementsByTagName('loc') as $loc)
                    {
                        $v = $loc -> nodeValue;
                        
                        if (!$this -> isValid($v) || $v == $this -> handler['url'])
                        {
                            continue;
                        }
                        
                        $domain = parse_url($v, PHP_URL_HOST);
                        if (!isset($this -> handler['sitemaps']) || !array_search($v, $this -> handler['sitemaps']))
                        {
                            $this -> handler['sitemaps'][] = $v;
                        }
                        if (!isset($this -> bin['sitemaps'][$domain]) || !array_search($v, $this -> bin['sitemaps'][$domain]))
                        {
                            $this -> bin['sitemaps'][$domain][] = $v;
                        }
                        $this -> pushLink($v, true);
                    }
                }
            }
            else
            {
                foreach ($smContent as $k => $v)
                {
                    if (!$this -> isValid($v) || $v == $this -> handler['url'])
                    {
                        continue;
                    }
                    
                    $domain = parse_url($v, PHP_URL_HOST);
                    if (!isset($this -> handler['sitemaps']) || !array_search($v, $this -> handler['sitemaps']))
                    {
                        $this -> handler['sitemaps'][] = $v;
                    }
                    if (!isset($this -> bin['sitemaps'][$domain]) || !array_search($v, $this -> bin['sitemaps'][$domain]))
                    {
                        $this -> bin['sitemaps'][$domain][] = $v;
                    }
                    $this -> pushLink($v, true);
                }
            }
        }
        elseif (preg_match('/xml/', $smContentType))
        {
            $smContent = $this -> LoadContent($url);
            if (strlen($smContent) > $truncateLimit)
            {
                return false;
            }
            
            $dom = new DOMDocument;
            $dom -> loadXML($smContent);
            
            if ($dom -> getElementsByTagName('sitemapindex') -> length)
            {
                if (ini_get('max_execution_time') != 0)
                {
                    $this -> loadSitemaps($dom -> getElementsByTagName('loc') -> item(0) -> nodeValue);
                }
                else
                {
                    foreach ($dom -> getElementsByTagName('loc') as $loc)
                    {
                        $this -> loadSitemaps($loc -> nodeValue);
                    }
                }
            }
            elseif ($dom -> getElementsByTagName('urlset') -> length)
            {
                foreach ($dom -> getElementsByTagName('loc') as $loc)
                {
                    $v = $loc -> nodeValue;
                    
                    if (!$this -> isValid($v) || $v == $this -> handler['url'])
                    {
                        continue;
                    }
                    
                    $domain = parse_url($v, PHP_URL_HOST);
                    if (!isset($this -> handler['sitemaps']) || !array_search($v, $this -> handler['sitemaps']))
                    {
                        $this -> handler['sitemaps'][] = $v;
                    }
                    if (!isset($this -> bin['sitemaps'][$domain]) || !array_search($v, $this -> bin['sitemaps'][$domain]))
                    {
                        $this -> bin['sitemaps'][$domain][] = $v;
                    }
                    $this -> pushLink($v, true);
                }
           }
        }
        elseif (preg_match('/text\/plain/', $smContentType))
        {
            $smContent = file($url);
            if (strlen(implode($smContent)) > $truncateLimit)
            {
                return false;
            }
            foreach ($smContent as $k => $v)
            {
                if (!$this -> isValid($v) || $v == $this -> handler['url'])
                {
                    continue;
                }
                
                $domain = parse_url($v, PHP_URL_HOST);
                if (!isset($this -> handler['sitemaps']) || !array_search($v, $this -> handler['sitemaps'])) {
                    $this -> handler['sitemaps'][] = $v;
                }
                if (!isset($this -> bin['sitemaps'][$domain]) || !array_search($v, $this -> bin['sitemaps'][$domain])) {
                    $this -> bin['sitemaps'][$domain][] = $v;
                }
                $this -> pushLink($v, true);
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Loading hidden links
     * @param string $url
     */
    function loadAjaxLinks($url)
    {
        $hiddenUrl = $this -> ajaxUrlTransform($url);
        
        $OpenCrawlerSon = new OpenCrawler;
        $OpenCrawlerSon -> loadUrl($hiddenUrl);
        foreach ($OpenCrawlerSon -> handler['a'] as $hiddenA)
        {
            $this -> pushLink($hiddenA);
        }
        unset($OpenCrawlerSon);
    }
    
    /**
     * Tranforming an URL from dynamic form to static (classic) form
     * @param string $dynUrl
     */
    function ajaxUrlTransform($dynUrl)
    {
        if ($urlQuery = parse_url($dynUrl, PHP_URL_QUERY))
        {
            parse_str($urlQuery, $urlQueryArray);
        }
        
        if ($fragment = @parse_url($dynUrl, PHP_URL_FRAGMENT))
        {
            if (preg_match('/^!/', $fragment))
            {
                $fragment = substr($fragment, 1);
            }
            else
            {
                return false;
            }
        }
        $urlQueryArray['_escaped_fragment_'] = (isset($fragment)) ? $fragment : '';
        
        $fixUrl = preg_replace('/^([^(\?|#)]+)(.*)$/', "$1?" . http_build_query($urlQueryArray), $dynUrl);
        
        return $fixUrl;
    }
}