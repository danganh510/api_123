<?php

namespace Score\Backend\Controllers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Score\Models\ScCronTour;
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

class Crawlertourv2Controller extends ControllerBase
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
        ini_set('max_execution_time', 20);

        $time_plus = $this->request->get("timePlus");
        $is_live = (bool)  $this->request->get("isLive");
        $has_standing = (bool)  $this->request->get("hasStanding");
        $this->type_crawl = $this->request->get("type");
        $total = 0;

        $time = microtime(true);

        if (!$this->type_crawl) {
            $this->type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
        }
        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "============================\r\n";
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";
        $start_time = microtime(true);
        $list_tour = [];
        $time_crawl = $this->my->formatDateYMD(time());

        $tourCron = ScCronTour::find([
            "cron_update_time != :TODAY: AND match_status = 'Y'",
            'bind' => [
                "TODAY" => $time_crawl
            ]
        ]);
        $strTour = array_column($tourCron->toArray(), "cron_tour_id");
        $strTour = implode(",", $strTour);
        $tourCrawlRepo = new TournamentCrawlRepo();
        $tour = $tourCrawlRepo->getTournamentToshow($strTour);

        //$tour = ScTournament::findFirst("2827");
        if (!$tour) {
            echo "Not found";
            die();
        }
        $cronModel = new ScCronTour();
        $cronModel->setCronTourId($tour->getTournamentId());
        $cronModel->setCronUpdateTime($time_crawl);
        $cronModel->setMatchStatus("N");
        $cronModel->save();

        echo "Tour Id: " . $tour->getTournamentId() . " \r\n";
        // var_dump(microtime(true) - $time);
        try {
            $crawler = new CrawlerList($this->type_crawl, $time_plus, $is_live, $tour->getTournamentHrefFlashscore(), $has_standing);
            $list_tour = $crawler->getInstance();
            //list_tour:
            //  'list_live_match' => $list_live_match,
            // 'tourInfoHome' => $tourInfoHome,
            // 'tourInfoAway' => $tourInfoAway,
            // 'tourInfoOveral'
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
                'list_match' => $list_tour['list_live_match'],
                'time_plus' => $time_plus,
                'type_crawl' => $this->type_crawl,
                'is_live' => (bool) $is_live,
                'is_list' => false,
            ];
            //        die(json_encode($request));


            $clientGuzzle = new \GuzzleHttp\Client();
            $url = API_END_PONT . '/save-match';
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
            $total = count($list_tour['list_live_match']);

            //lưu tour:
            //
            foreach ($list_tour['tourInfoOveral'] as $standingOveral) {
                var_dump($standingOveral);exit;
            }

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
    public function saveTournamentStanding($standing)
    {
    }
}
