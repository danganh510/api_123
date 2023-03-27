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
use Score\Models\ScTournament;
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
            $limit = 1;

            $countryModel = ScCountry::findFirst([
                'country_order = 1'
            ]);
            if (!$countryModel) {
                echo "notfound Country";
                die();
            }

            $countryModel->setCountryOrder(2);
            $countryModel->save();

            $selenium = new Selenium($this->url_fl);
            try {
                $selenium->clickButton("#onetrust-reject-all-handler");
            } catch (Exception $e) {
                echo "not found cookie";
            }

            $selenium->clickButton(".lmc__itemMore");
            $blockCountry = $selenium->findElements(".lmc__block");

            $arrCountryCrawl = [];
            $arrTour = [];
            $total = 0;

            foreach ($blockCountry as $divCountry) {
                $countryName = $divCountry->getText();
 
                if ($countryName != $countryModel->getCountryName()) {
                    continue;
                }
                echo $countryName;
                echo $countryModel->getCountryName();
                $selenium->quit();
                exit;
                $arrCountryCrawl[] = $countryName;
                $type = "country";

                if (in_array($countryName, ['Africa', 'Asia', 'Australia & Oceania', 'Europe', 'North & Central America', 'South America'])) {
                    //tour in area
                    $type = "area";
                }
                if ($countryName == "World") {
                    $type = "global";
                }
                $divCountry->click();
                sleep(2);
                $arrDivTour = $divCountry->findElements(WebDriverBy::cssSelector(".lmc__templateHref"));
                var_dump(count($arrDivTour));
                $selenium->quit();
                die();

                foreach ($arrDivTour as $key =>  $tour) {
                    $tourName = $tour->getText();
                    $href = $tour->getAttribute('href');
                    $arrTour[$key] = [
                        'name' => $tourName,
                        'slug' => MyRepo::create_slug($tourName),
                        'href' => $href,
                        'type' => $type,
                        'countryName' => $countryName
                    ];
                    $tournament = new ScTournament();
                    $tournament->setTournamentName($arrTour[$key]['name']);
                    $tournament->setTournamentSlug($arrTour[$key]['slug']);
                    $tournament->setTournamentImage("");
                    $tournament->setTournamentType($arrTour[$key]['type']);

                    $tournament->setTournamentNameFlashScore($tourName);
                    $tournament->setTournamentHrefFlashscore($href);
                    $tournament->setTournamentActive("Y");
                    $tournament->setTournamentOrder($key);

                    $tournament->setTournamentCountry($arrTour[$key]['countryName']);
                    $tournament->setTournamentCountryCode($countryModel->getCountryCode());
                    $save = $tournament->save();
                    if (!$save) {
                        echo $tournament->getMessages();
                        die();
                    }
                }
                $total++;
            }


            echo (microtime(true) - $start_time) . "</br>";
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
        $selenium->quit();
        var_dump($arrCountryCrawl, $arrTour);
        exit;

        echo (microtime(true) - $start_time) . "</br>";
        end:
        echo "---total: " . $total;

        echo "---finish in " . (time() - $start_time_cron) . " second";
        die();
    }
}
