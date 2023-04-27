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

class CrawlerController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;

    public function indexAction()
    {

        ini_set('max_execution_time', -1);

        $time_plus = $this->request->get("timePlus");
        $is_live = (bool) $this->request->get("isLive");
        $off = $this->request->get("off");
        if ($off == 1) {
            $currentHour = date('G');
            $currentMinutes = date('i');
            if ($currentHour >= 0 && $currentHour <= 1 && $currentMinutes >= 0 && $currentMinutes < 15) {
                echo "Now is: " . $currentHour . " Hour " . $currentMinutes . " Minutes \r\n";
                die();
            }
            //7h tới 11h tối thứ 7 cn tắt detail
            $dayOfWeek = date('N', time()); // Lấy số thứ tự của ngày trong tuần
            echo "Today is: " . $dayOfWeek . " and " . $currentHour . " Hour \r\n";
            if ($dayOfWeek == 6 || $dayOfWeek == 7) {

                if ($currentHour >= 8 && $currentHour <= 20) {
                    echo "gio cao diem";
                    die();
                }
            }
        } elseif ($off == 2) {
            $currentHour = date('G');
            $currentMinutes = date('i');
            if ($currentHour >= 0 && $currentHour <= 1 && $currentMinutes >= 0 && $currentMinutes < 15) {
                echo "Now is: " . $currentHour . " Hour " . $currentMinutes . " Minutes \r\n";
                die();
            }
            $dayOfWeek = date('N', time()); // Lấy số thứ tự của ngày trong tuần
            echo "Today is: " . $dayOfWeek . " and " . $currentHour . " Hour \r\n";
            if ($dayOfWeek == 6 || $dayOfWeek == 7) {

                if ($currentHour >= 11 && $currentHour <= 16) {
                    echo "gio cao diem";
                    die();
                }
            }
        }

        $this->type_crawl = $this->request->get("type");
        $total = 0;


        if (!$this->type_crawl) {
            $this->type_crawl = MatchCrawl::TYPE_SOFA;
        }
        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";
        $start_time = microtime(true);
        $list_match = [];
        try {
            $crawler = new CrawlerList($this->type_crawl, $time_plus, $is_live);
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
                'is_list' => true,
            ];
            //   die(json_encode($request));exit;
            $clientGuzzle = new \GuzzleHttp\Client();
            $url = API_END_PONT . '/save-match';
            try {
                $response = $clientGuzzle->post(
                    $url,
                    array(
                            //      'headers' => $header,
                        RequestOptions::JSON => $request,
                        RequestOptions::SYNCHRONOUS => true,
                        // send the request synchronously
                    )
                );
            } catch (Exception $e) {
            }
            $start_time_call = microtime(true);
            $result = json_decode($response->getBody()->getContents(), true);

            $total = isset($result['total']) ? $result['total'] : 0;
            echo "status: " . $total;
            //   echo $response->getBody()->getContents();

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