<?php

namespace Score\Backend\Controllers;

use Exception;
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

class Crawlerv2Controller extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;

    public function indexAction()
    {

        ini_set('max_execution_time', -1);

        $time_plus = $this->request->get("timePlus");
        $is_live = (bool)  $this->request->get("isLive");
        $this->type_crawl = $this->request->get("type");
        $isDeleteCache = (bool) $this->request->get("deleteCache");
        $total = 0;
        if ($isDeleteCache == true) {
            goto delete_cache;
        }

        if (!$this->type_crawl) {
            $this->type_crawl = MatchCrawl::TYPE_SOFA;
        }
        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "/n/r";
        $start_time = microtime(true);
        try {
            $crawler = new CrawlerList($this->type_crawl, $time_plus, $is_live);
            $list_match = $crawler->getInstance();

            //time plus = 1  crawl all to day
            // $divParent = $crawler->getDivParent($seleniumDriver, $time_plus);
            // $seleniumDriver->quit();
            // echo (microtime(true) - $start_time) . "</br>";
        } catch (Exception $e) {
            echo $e->getMessage();
            // $seleniumDriver->quit();
            die();
        }
        $cacheTeam = new CacheTeam();
        $arrTeamOb = $cacheTeam->getCache();

        $arrMatchCrawl = [];
        $is_new = false;
        //start crawler
        try {
            statCrawler:
            // $list_match = $crawler->CrawlMatchScore($divParent);
            // echo (microtime(true) - $start_time) . "</br>";
            listMatch:
            $matchRepo = new MatchRepo();
            foreach ($list_match as $match) {
                $home = Team::saveTeam($match->getHome(), $match->getHomeImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl);
                $away = Team::saveTeam($match->getAway(), $match->getAwayImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl);
                $tournament = Tournament::saveTournament($match->getTournament(), $this->type_crawl);

                if (!$home) {
                    echo "can't save home team";
                    continue;
                }
                if (!$away) {
                    echo "can't save away team";
                    continue;
                }
                if (!$tournament) {
                    echo "can't save tournament team";
                    continue;
                }
                $result =  $matchRepo->saveMatch($match, $home, $away, $tournament, $time_plus, $this->type_crawl);
                if ($result['matchSave']) {
                    $arrMatchCrawl[] = $result['matchSave'];
                    $total++;
                    //  echo "Save match success --- ";
                } else {
                    echo "Save match false ---";
                }
                if ($result['is_new']) {
                    $is_new = true;
                }
            }
            $total++;
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
        delete_cache:
        if (($is_live !== true && $total > 1) || $isDeleteCache == true) {
            //cache match trong vòng 14 ngày
            $timestamp_before_7 = time() - 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
            $timestamp_affter_7 = time() + 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
            $arrMatch = ScMatch::find(
                "match_start_time > $timestamp_before_7 AND match_start_time < $timestamp_affter_7"
            );
            $arrMatch = $arrMatch->toArray();
            $matchCache = new CacheMatch();
            $matchCache->setCache(json_encode($arrMatch));
        } else {
            //cache match trong vòng 1 ngày
            $matchCache = new CacheMatchLive();
            $matchCache->setCache(json_encode($arrMatchCrawl));
        }

        //nếu có trận được tạo mới thì cache lại:
        if ($is_new || ($is_live !== true && $total > 1)) {
            $arrTeam = ScTeam::find("team_active = 'Y'");
            $arrTeam = $arrTeam->toArray();
            $arrTeamCache = [];
            foreach ($arrTeam as $team) {
                $arrTeamCache[$team['team_id']] = $team;
            }
            $teamCache = new CacheTeam();
            $teamCache->setCache(json_encode($arrTeamCache));

            echo "cache total: " . count($arrTeamCache) . " team /r/n";
            //cache tour
            $arrTour = ScTournament::find("tournament_active = 'Y'");
            $arrTour = $arrTour->toArray();
            $arrTourCache = [];
            foreach ($arrTour as $tour) {
                $arrTourCache[$tour['tournament_id']] = $tour;
            }
            $tourCache = new CacheTour();
            $tourCache->setCache(json_encode($arrTourCache));
        }

        echo "---total: " . $total;

        echo "---finish in " . (time() - $start_time_cron) . " second \n\r";
        die();
    }


    /**
     * Gets the full code source of HTML page even if using ajax
     *
     * In php a simple file_get_content or a curl request could do the job except if the page is build with dynamic content (ajax request).
     * In that case wa have to emulate a full browser behavior to get full HTML content generated by javascript.
     *
     * @param $url url to crawl
     * @return $html_content url html content
     */
    function get_code_source($url)
    {
        $html_content = null;

        # Decode url if needed
        $url = trim(urldecode($url));

        # Check url is not empty
        if ($url != '') {
            # Check http:// or https:// for further request or add it
            if (!stristr($url, 'http://') and !stristr($url, 'https://')) {
                $url = 'http://' . $url;
            }

            $url_segs = parse_url($url);

            # Check url contains a hostname
            if (isset($url_segs['host'])) {

                # Define usefull paths
                $here = dirname(__FILE__) . DIRECTORY_SEPARATOR;
                $bin_files = $here . 'bin' . DIRECTORY_SEPARATOR;
                $jobs = $here . 'jobs' . DIRECTORY_SEPARATOR;

                # Change Url to Filename
                $file_name = $this->sanitize($url) . ".html";

                # Check existence or create jobs directory
                if (!is_dir($jobs)) {
                    mkdir($jobs);
                    /*file_put_contents($jobs . 'index.php', '<?php exit(); ?>');*/
                }

                # Clean url
                $url = strip_tags($url);
                $url = str_replace(';', '', $url);
                $url = str_replace('"', '', $url);
                $url = str_replace('\'', '/', $url);
                $url = str_replace('<?', '', $url);
                $url = str_replace('<?', '', $url);
                $url = str_replace('\077', ' ', $url);

                # Protect url
                $url = escapeshellcmd($url);

                # Create phantomjs script
                $src = "
                var page = new WebPage();
                page.settings.userAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:16.0) Gecko/20120815 Firefox/16.0';
                var fs = require('fs');
                page.onLoadFinished = function(status) {
                    fs.write('{$file_name}', page.content, 'w');
                    phantom.exit();
                }
                page.open('{$url}');
            ";

                # Create job file
                $job_file = $jobs . $url_segs['host'] . crc32($src) . '.js';

                # Fill in job file
                file_put_contents($job_file, $src);

                # Create executable command
                $exec = $bin_files . 'phantomjs ' . $job_file;

                # Protect shell special char
                $escaped_command = escapeshellcmd($exec);

                # Run phantomjs script
                exec($escaped_command);

                # Retrieve url code source
                $html_content = file_get_contents($here . $file_name);

                # Delete html file (or not ... depending on what you want to do)
                unlink($here . $file_name);

                # Delete job file
                unlink($job_file);

                # Delete job directory
                rmdir($jobs);
            }
        }

        return $html_content;
    }

    function sanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = array(
            "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?"
        );
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
            $clean;
    }
}
