<?php

namespace Score\Backend\Controllers;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Goutte\Client;
use GuzzleHttp\Psr7\Request;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Models\ScCountry;
use Score\Repositories\CrawlerScore;
use Score\Repositories\CrawlerSofaDetail;
use Score\Repositories\Team;

use Score\Models\ScMatch;
use Score\Repositories\Country;
use Score\Repositories\CrawlerFlashScore;
use Score\Repositories\CrawlerList;
use Score\Repositories\CrawlerSofa;
use Score\Repositories\MatchCrawl;
use Score\Repositories\MatchRepo;
use Score\Repositories\MyRepo;
use Score\Repositories\Selenium;
use Score\Repositories\Tournament;


class CrawlerstructureController extends ControllerBase
{
    public $url_fl = "https://www.flashscore.com";
    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
    public function indexAction()
    {

        ini_set('max_execution_time', 20);

        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";

        $start_time = microtime(true);
        try {
            $arrCountry = ScCountry::find();
            $arrCountryName = array_column($arrCountry->toArray(),"country_name");

            $selenium = new Selenium($this->url_fl);
            $selenium->clickButton(".leftMenu__icon--arrow");
            $blockCountry = $selenium->findElements(".lmc__block");

            $arrCountryCrawl = [];
            $arrTour = [];

            foreach ($blockCountry as $divCountry) {
                $countryName = $divCountry->getText();

                if (in_array($countryName,$arrCountryName) || in_array($countryName,$arrCountryCrawl)) {
                    continue;
                }
                $arrCountryCrawl[] = $countryName;
                $butonOpen = $divCountry->findElement(WebDriverBy::cssSelector(".lmc__sortIcon"));
                $butonOpen->click();
                $arrDivTour = $divCountry->findElements(WebDriverBy::cssSelector(".lmc__templateHref"));
                
                foreach ($arrDivTour as $tour) {
                    $tourName = $tour->getText();
                    $href = $tour->getAttribute('href');
                    $arrTour[] = [
                        'name' => $tourName,
                        'href' => $href,
                        'countryName' => $countryName
                    ];
                }
            }
            $selenium->quit();

            echo ( microtime(true) - $start_time). "</br>";
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
        var_dump($arrCountryCrawl,$arrTour);exit;
  
        echo ( microtime(true) - $start_time). "</br>";
        end:
        echo "---total: ". $total;

        echo "---finish in " . (time() - $start_time_cron) . " second";
        die();
    }
}
