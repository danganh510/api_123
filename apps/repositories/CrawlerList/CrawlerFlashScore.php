<?php

namespace Score\Repositories;

use Exception;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Goutte\Client;
use Score\Models\ScCountry;
use Score\Models\ScCron;

class CrawlerFlashScore extends CrawlerFlashScoreBase
{
    public function __construct($seleniumDriver, $url_crawl, $day_time, $isLive)
    {
        $this->seleniumDriver = $seleniumDriver;
        $this->url_crawl = $url_crawl;
        $this->day_time = $day_time;
        $this->isLive = $isLive;
    }
    public function saveFile($cronModel)
    {

        if (!$cronModel) {
            $this->deleteFolder();
            $cronModel = new ScCron();
            $cronModel->setCronTime($this->day_time);
            $cronModel->setCronDay($this->my->getDays(time(),strtotime($this->day_time)));
            $cronModel->setCronStatus("Y");
            $cronModel->Save();
        }
        if ($cronModel && $cronModel->getCronStatus() == "N") {
            $today = $this->my->formatDateYMD(time());
            if ($this->day_time == $today) {
                $cronModel->setCronStatus("Y");
                $cronModel->save();
            } else {
                $this->deleteFolder();
                echo "All Match save";
                die();
            }
        }


        $this->setupSite();
        try {
            //tìm tất cả các div rồi lưu lại vào file
            $parentDivs = $this->seleniumDriver->findElements('div[id="live-table"] > section > div > div >div');
            $check_exist_file = $this->checkFileCache();
            $total_div = count($parentDivs);

            $key_now = 0;
            foreach ($parentDivs as $key =>  $parentDiv) {
                if ($check_exist_file) {
                    if ($key <= 600) {
                        continue;
                    }
                } else {
                    if ($key > 600) {
                        break;
                    }
                }
                $key_now = $key;
                $htmlDiv = $parentDiv->getAttribute("outerHTML");
                $this->saveDivToFile($htmlDiv, $key);
            }
        } catch (Exception $e) {
            echo "error118:";
            echo $e->getMessage();
        }

        if ($key_now + 1 >= $total_div) {
            $cronModel->setCronStatus("N");

            //tìm cron chạy ngày hnay để xóa đi và chạy lại
            $today = $this->my->formatDateYMD(time());
            $cronModelToday = ScCron::findFirst([
                'cron_time = :time: AND cron_day = :day_plus:',
                'bind' => [
                    'time' => $today,
                    'day_plus' => $this->my->getDays(time(),strtotime($this->day_time))
                ]
            ]);
            if ($cronModelToday) {
                $cronModelToday->delete();
            } 
        }
        if ($cronModel) {
            $cronModel->setCronCount($total_div);
            $cronModel->save();
        }
        $this->seleniumDriver->quit();
    }
    public function crawlList()
    {
        $cronModel = ScCron::findFirst([
            'cron_time = :date: AND cron_day = :day_plus:',
            'bind' => [
                'date' => $this->day_time,
                'day_plus' => $this->my->getDays(time(),strtotime($this->day_time))
            ]
        ]);
        $cronModelNo = ScCron::findFirst([
            'cron_status = "Y" AND cron_day = :day_plus:',
            'day_plus' => $this->my->getDays(time(),strtotime($this->day_time))
        ]);
        if ($cronModelNo && $cronModelNo->getCronTime() != $this->day_time) {
            $cronModelNo->setCronStatus("N");
            $cronModelNo->save();
        }
        if (!$cronModel || $cronModel->getCronStatus() == "Y") {
            $this->saveFile($cronModel);
            echo "Cache Match";
            return [];
        }
        if (!$this->checkFileCache()) {
            echo "No has cache Match";
            return [];
        }
        require_once(__DIR__ . "/../../library/simple_html_dom.php");

        $list_live_match  = [];
        for ($i = 0; $i < $cronModel->getCronCount(); $i++) {
            //   goto test;
            try {
                $div =  str_get_html("<div>" . $this->getDivHtml($i) . "</div>");
                $div = $div->find("div", 0);
                //check tournament
                $divTuornaments = $div->find('.event__title--type');
                if (!empty($divTuornaments)) {
                    //đây là div chứa tournament
                    $this->list_live_tournaments[] = $this->getTournament($div);
                    continue;
                }

                //match
                $divMatch = $div->find(".event__participant", 0);

                if (!empty($divMatch)) {
                    $divMatch = $div->find("div", 0);
                    $list_live_match[] = $this->getMatch($divMatch, $this->round);

                    // echo "time get match: " . (microtime(true) - $time_1) . "</br>";
                }
            } catch (Exception $e) {
                echo "1-";
                continue;
            }
            test:
            // $text = $div->getAttribute("outerHTML");
            // $this->saveText($text, $key);
        }
        $this->deleteFolder();
        return $list_live_match;
    }
}
