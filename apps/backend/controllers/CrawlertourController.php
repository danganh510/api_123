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

class CrawlertourController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;

    public function indexAction()
    {

        ini_set('max_execution_time', -1);

        $time_plus = $this->request->get("timePlus");
        $is_live = (bool)  $this->request->get("isLive");
        $this->type_crawl = $this->request->get("type");
        $total = 0;


        if (!$this->type_crawl) {
            $this->type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
        }
        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";
        $start_time = microtime(true);
        $list_match = [];

        $tour = ScTournament::findFirst("tournament_is_crawling = 'Y'");
        $tour->setTournamentIsCrawling("N");
        $tour->save();
        try {
            $crawler = new CrawlerList($this->type_crawl, $time_plus, $is_live,$tour->getTournamentHrefFlashscore());
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
                'is_live' => $is_live,
                'tour' => true
            ];
   
            $clientGuzzle = new \GuzzleHttp\Client();
            $url = 'http://123tiso.com/save-match';
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
