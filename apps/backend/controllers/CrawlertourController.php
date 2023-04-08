<?php

namespace Score\Backend\Controllers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Score\Models\ScMatch;
use Score\Repositories\Team;


use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Score\Repositories\CacheMatch;
use Score\Repositories\CacheMatchLive;
use Score\Repositories\CacheTeam;
use Score\Repositories\CacheTour;
use Score\Repositories\CrawlerList;
use Score\Repositories\MatchCrawl;
use Score\Repositories\MatchRepo;
use Score\Repositories\Tournament;
use Score\Repositories\TournamentCrawlRepo;

class CrawlertourController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;

    public function indexAction()
    {
        $currentHour = date('G');
        $currentMinutes = date('i');
        if ($currentHour >= 0 && $currentHour <= 1 && $currentMinutes >= 0 && $currentMinutes < 15) {
            echo "Now is: " . $currentHour . " Hour " . $currentMinutes . " Minutes \r\n";
            die();
        }

        ini_set('max_execution_time', -1);

        $time_plus = $this->request->get("timePlus");
        $is_live = (bool)  $this->request->get("isLive");
        $this->type_crawl = $this->request->get("type");
        $is_nomal = $this->request->get("isNomal");
        $total = 0;

        $time = microtime(true);

        if (!$this->type_crawl) {
            $this->type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
        }
        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "============================\r\n";
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";
        $start_time = microtime(true);
        $list_match = [];
        $enpoint =  API_END_PONT . "/get-list-match";
        $clientGuzzle = new \GuzzleHttp\Client();
        try {
            $arrListMatchLive = $clientGuzzle->get(
                $enpoint
            );
        } catch (Exception $e) {
        }
        if (empty($arrListMatchLive->getBody())) {
            echo "Not found match\n\r";
            die();
        }
        $arrListMatchLive = json_decode($arrListMatchLive->getBody(), true);
        if (empty($arrListMatchLive)) {
            echo  "Not found match\n\r";
            die();
        }
        $arrTourId = array_keys($arrListMatchLive);
        $strTour = implode(",", $arrTourId);
        $tourCrawlRepo = new TournamentCrawlRepo();
        if ($is_nomal) {
            $tour = $tourCrawlRepo->getTournamentNomal($strTour);
        } else {
            $tour = $tourCrawlRepo->getTournament($strTour);
        }
        //$tour = ScTournament::findFirst("2827");
        if (!$tour) {
            echo "Not found";
            die();
        }

        $tour->setTournamentIsCrawling("N");
        $tour->save();
        echo "Tour Id: " . $tour->getTournamentId() . " \r\n";
        // var_dump(microtime(true) - $time);
        try {
            $crawler = new CrawlerList($this->type_crawl, $time_plus, $is_live, $tour->getTournamentHrefFlashscore());
            $list_match = $crawler->getInstance();
        } catch (Exception $e) {
            echo $e->getMessage();
            // $seleniumDriver->quit();
            die();
        }

        $arrMatchCrawl = [];
        $is_new = false;
        //start crawler

        try {
            statCrawler:
            // $list_match = $crawler->CrawlMatchScore($divParent);
            // echo (microtime(true) - $start_time) . "</br>";
            listMatch:
            $request = [
                'list_match' => $list_match,
                'time_plus' => $time_plus,
                'type_crawl' => $this->type_crawl,
                'is_live' => (bool) $is_live,
                'is_list' => false,
            ];
            //        die(json_encode($request));


            $clientGuzzle = new \GuzzleHttp\Client();
            $url = API_END_PONT.'/save-match';
            try {
                $clientGuzzle->post(
                    $url,
                    array(
                        //      'headers' => $header,
                        RequestOptions::JSON => $request,
                        RequestOptions::SYNCHRONOUS => true, // send the request synchronously
                    )
                );
            } catch (Exception $e) {
            }
            $start_time_call = microtime(true);
            //        $result = json_decode($response->getBody()->getContents(),true);
            $total = count($list_match);
            echo "status: " . $total;

            // if ($total < 10) {
            //     sleep(5);
            //     goto statCrawler;
            // }
        } catch (Exception $e) {
            echo $total;
            echo $e->getMessage();
        }

        // $seleniumDriver->quit();
        // echo (microtime(true) - $start_time) . "</br>";

        echo "---total: " . $total;

        echo "---finish in " . (time() - $start_time_cron) . " second \n\r";
        die();
    }
}
