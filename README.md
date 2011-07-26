Welcome to the OpenCrawler wiki!
OpenCrawler is an open source web scraper written in PHP and in a standalone file.

## What is Open Crawler?
Open Crawler is a web spider (which can be freey interpretated as the spider that just we know, 
because of the form of the WWW just like the web of the spider) which does go all around the web and catch detailed 
informations for every web site is just visiting, just like Google do when it does index your website (or also like Bing or Yahoo).

## Why Open Crawler?
Open Crawler was born as an Open Source project (so any programmer can freely contribute or modify the source code), 
to let people to have an indexer by their own, freely (and for free, of course) licensed with the 
[GNU GPL license](http://www.gnu.org/licenses/gpl.html). It can be used in many area that can be from a personal 
Search Engine (just like Google) to a small website search (so you don't have to use any third part search engines 
into your website).

## Who can use Open Crawler?
Any person who like the World Wide Web, and of course all the professional programmers, or not ;)

## Example usage
    require_once 'OpenCrawler.php';
    $OpenCrawler = new OpenCrawler();
    $OpenCrawler -> loadUrl('http://www.example.com/');
    
    // magic things...
    
    // Debug informations
    echo '<pre>';
    print_r($OpenCrawler);