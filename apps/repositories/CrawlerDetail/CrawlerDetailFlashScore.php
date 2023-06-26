<?php

namespace Score\Repositories;

use ConstEnv;
use DOMDocument;
use Exception;


class CrawlerDetailFlashScore extends CrawlerDetail
{
    public function __construct($seleniumDriver, $url_crawl, $day_time, $isLive, $language = "en")
    {
        $this->seleniumDriver = $seleniumDriver;
        $this->url_crawl = $url_crawl;
        $this->isLive = $isLive;
        $this->language = $language;
    }
    public function crawlDetail()
    {
        require_once(__DIR__ . "/../../library/simple_html_dom.php");
        $result = [];
        $this->getDivParent();
        $info = $this->crawlDetailInfo();
        if (empty($info)) {
            return false;
        }
        $start = $this->crawlDetailStarts();
        $tracker = $this->crawlDetailTracker();
        $match = $this->crawlDetailMatch();
        // $video = $this->crawlDetailVideo();

        if (!$this->divInfo && !$this->divStart && !$this->divTracker) {
            return false;
        }

        $result = [
            'match' => $match,
            'info' => $info,
            'start' => $start,
            'tracker' => $tracker,
            'video' => $this->divVideo
        ];
        return $result;
    }
    public function checkHasExist()
    {
        $parentDiv = $this->seleniumDriver->findElement('p > strong');
        if ($parentDiv->text() == "Error:") {
            return true;
        }
        return false;
    }
    /**
     * @ $this->seleniumDriver Selenium
     */
    public function getDivParent()
    { #/match-summary/match-statistics
        try {
            //$html = $this->seleniumDriver->getPageSource();
            //  $this->seleniumDriver->clickButton('.filters__tab > .filters');
            $this->divInfo = $this->getDivInfo();
            if (!$this->divInfo) {
                goto end;
            }
            $this->divStart = $this->getDivStart();
            $this->divTracker = $this->getDivTracker();
            $this->divVideo = $this->getDivVideo();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        echo $this->seleniumDriver->checkRam();
        end:
        $this->seleniumDriver->quit();
    }
    public function getDivInfo()
    {
        try {
            $parentDiv = $this->seleniumDriver->findElement('div[id="detail"]');
        } catch (Exception $e) {
            echo "Tran đấu đã bị hủy \r\n";
            return "";
        }


        $htmlDivInfo = $parentDiv->getAttribute("outerHTML");

        $htmlDivInfo = "<!DOCTYPE html>" . $htmlDivInfo;
        //khai bao cho the svg
        $htmlDivInfo = str_replace(["<svg ", "/svg>"], ["<div ", "/div>"], $htmlDivInfo);
        return $htmlDivInfo;
    }
    public function getDivStart()
    {
        $htmlDivStart = "";
        if ($this->language == "en") {
            $href = ConstEnv::HREF_DETAIL_START_EN;
        } else {
            $href = ConstEnv::HREF_DETAIL_START_VI;
        }

        try {
            $this->seleniumDriver->clickButton("a[href='#$href']");
        } catch (Exception $e) {
            goto end;
        }
        sleep(0.5);
        $parentDiv = $this->seleniumDriver->findElement('div[id="detail"]');
        if (!$parentDiv)
            return "";
        $htmlDivStart = $parentDiv->getAttribute("outerHTML");

        $htmlDivStart = "<!DOCTYPE html>" . $htmlDivStart;
        //khai bao cho the svg
        $htmlDivStart = str_replace(["<svg ", "/svg>"], ["<div ", "/div>"], $htmlDivStart);
        end:
        return $htmlDivStart;
    }
    public function getDivTracker()
    {

        $htmlTRacker = "";
        if ($this->language == "en") {
            $href = ConstEnv::HREF_DETAIL_TRACKER_EN;
        } else {
            $href = ConstEnv::HREF_DETAIL_TRACKER_VI;
        }
        try {
            $this->seleniumDriver->clickButton("a[href='#$href']");
        } catch (Exception $e) {
            goto end;
        }
        sleep(0.5);
        $parentDiv = $this->seleniumDriver->findElement('div[id="detail"]');
        if (!$parentDiv)
            return "";
        $htmlTRacker = $parentDiv->getAttribute("outerHTML");

        $htmlTRacker = "<!DOCTYPE html>" . $htmlTRacker;
        //khai bao cho the svg
        $htmlTRacker = str_replace(["<svg ", "/svg>"], ["<div ", "/div>"], $htmlTRacker);

        end:
        return $htmlTRacker;
    }
    public function getDivVideo()
    {
        $url_video = "";
        if ($this->language == "en") {
            $href = ConstEnv::HREF_DETAIL_VIDIEO_EN;
        } else {
            $href = ConstEnv::HREF_DETAIL_VIDIEO_VI;
        }
        try {
            $this->seleniumDriver->clickButton("a[href='#$href']");
            sleep(0.5);

            $videoElement = $this->seleniumDriver->findElement(".videoInner > object");
            if ($videoElement) {
                $url_video = $videoElement->getAttribute("data");
            } else {
                $this->seleniumDriver->clickButton(".videoInner");
                $url_video = $this->seleniumDriver->waitGetUrl();
            }
        } catch (Exception $e) {
            echo "not found video 1";
            goto end;
        }

        // try {
        //     $this->seleniumDriver->clickButton(".videoInner");
        //     $url_video = $this->seleniumDriver->waitGetUrl();
        // } catch (Exception $e) {
        //     echo "not found video 2";
        // }

        end:
        return $url_video;
    }
    public function crawlDetailVideo()
    {
        //$divCrawl =  str_get_html($this->divVideo);

    }
    public function crawlDetailMatch()
    {
        $divCrawl = str_get_html($this->divInfo);
        $homeScore = "";
        $awayScore = "";
        $time = "";
        $divScore = $divCrawl->find(".detailScore__matchInfo > div > span");
        $startTime = $divCrawl->find(".duelParticipant__startTime > div", 0);
        $divTime = $divCrawl->find(".detailScore__matchInfo > div > .eventTime", 0);
        if ($startTime) {
            $startTime = $startTime->text();
        }
        if (isset($divScore[0])) {
            $homeScore = $divScore[0]->innertext();
        }
        if (isset($divScore[2])) {
            $awayScore = $divScore[2]->innertext();
        }
        if ($divTime) {
            $time = $divTime->text();
        } else {
            $divStatus = $divCrawl->find(".detailScore__matchInfo > div > .fixedHeaderDuel__detailStatus", 0);

            if ($divStatus) {
                $time = $divStatus->text();
            }
        }
        if (!$time) {
            $divStatus = $divCrawl->find(".detailScore__status", 0);
            if ($divStatus) {
                $time = $divStatus->text();
            }
        }
        $logo = $divCrawl->find(".participant__image");
        $homeName = $divCrawl->find(".participant__participantName", 1)->text();
        $awayName = $divCrawl->find(".participant__participantName", 3)->text();

        return [
            'homeName' => $homeName,
            'awayName' => $awayName,
            'homeScore' => $homeScore,
            'awayScore' => $awayScore,
            'homeLogo' => $logo[0]->getAttribute("src"),
            'awayLogo' => $logo[1]->getAttribute("src"),
            'timeNow' => $time,
            'startTime' => $startTime
        ];
    }

    public function crawlDetailInfo()
    {
        $info = [];
        $divCrawl = str_get_html($this->divInfo);
        var_dump($divCrawl);exit;
        if (!$divCrawl) {
            return [];
        }

        $divsInfo = $divCrawl->find("div[elementtiming='SpeedCurveFRP'] > div");
        foreach ($divsInfo as $div) {
            $temp = [];
            $timeNow = 0;
            $homeEvent = "";
            $homeDescription = "";
            $homeText = "";
            $assistHome = "";
            $homeSubIncident = "";
            $homeincidentSubOut = "";
            $homeScore = "";

            $awayEvent = "";
            $awayDescription = "";
            $awayText = "";
            $assistAway = "";
            $awaySubIncident = "";
            $awayincidentSubOut = "";
            $awayScore = "";
            $classDiv = $div->getAttribute('class');

            if (strpos($classDiv, 'smv__homeParticipant') !== false) {
                $time = $div->find(".smv__timeBox", 0);
                if ($time) {
                    $timeNow = $time->text();
                }

                $description = $div->find("div > .smv__incidentIcon", 0);
                if (!$description) {
                    $description = $div->find("div > .smv__incidentIconSub", 0);
                }
                if ($description) {
                    $arrEvent = $this->getEvent($description);

                    $homeEvent = $arrEvent['event'];
                    $homeDescription = $arrEvent['strDescription'];
                }

                $home = $div->find("a", 0);
                if ($home) {
                    $homeText = $home->text();
                }
                $assist = $div->find(".smv__assist", 0);
                if ($assist) {
                    $assistHome = $assist->text();
                }
                $supIncident = $div->find(".smv__subIncident", 0);
                if ($supIncident) {
                    $homeSubIncident = $supIncident->text();
                }
                $incidentSubOut = $div->find(".smv__incidentSubOut", 0);
                if ($incidentSubOut) {
                    $homeincidentSubOut = $incidentSubOut->text();
                }
                $homeScoreDiv = $div->find(".smv__incidentHomeScore", 0);
                if ($homeScoreDiv) {
                    $homeScore = $homeScoreDiv->text();
                }
            }
            if (strpos($classDiv, 'smv__awayParticipant') !== false) {
                $time = $div->find(".smv__timeBox", 0);
                if ($time) {
                    $timeNow = $time->text();
                }
                $description = $div->find("div > div", 1);

                if ($description) {
                    $arrEvent = $this->getEvent($description);
                    $awayEvent = $arrEvent['event'];
                    $awayDescription = $arrEvent['strDescription'];
                }

                $away = $div->find("a", 0);
                if ($away) {
                    $awayText = $away->text();
                }
                $assist = $div->find(".smv__assistAway", 0);
                if ($assist) {
                    $assistAway = $assist->text();
                }
                $supIncident = $div->find(".smv__subIncident", 0);
                if ($supIncident) {
                    $awaySubIncident = $supIncident->text();
                }

                $incidentSubOut = $div->find(".smv__incidentSubOut", 0);
                if ($incidentSubOut) {
                    $awayincidentSubOut = $incidentSubOut->text();
                }
                $awayScoreDiv = $div->find(".smv__incidentAwayScore", 0);
                if ($awayScoreDiv) {
                    $awayScore = $awayScoreDiv->text();
                }
            }

            if ($timeNow) {
                $info[] = [
                    'time' => $timeNow,
                    'homeText' => $homeText,
                    'homeAssist' => str_replace(["(", ")"], ["", ""], $assistHome),
                    'homeEvent' => $homeEvent,
                    'homeDescription' => $homeDescription,
                    'homeSubIncident' => str_replace(["(", ")"], ["", ""], $homeSubIncident),
                    'homeincidentSubOut' => str_replace(["(", ")"], ["", ""], $homeincidentSubOut),
                    'homeScore' => $homeScore,

                    'awayText' => $awayText,
                    'awayAssist' => str_replace(["(", ")"], ["", ""], $assistAway),
                    'awayEvent' => $awayEvent,
                    'awayDescription' => $awayDescription,
                    'awaySubIncident' => str_replace(["(", ")"], ["", ""], $awaySubIncident),
                    'awayincidentSubOut' => str_replace(["(", ")"], ["", ""], $awayincidentSubOut),
                    'awayScore' => $awayScore,
                ];
            }
        }

        return $info;
    }
    public function crawlDetailTracker()
    {
        $tracker = [];
        $divCrawl = str_get_html($this->divTracker);
        if (!$divCrawl) {
            return $tracker;
        }

        $divsStart = $divCrawl->find(".soccer__row");
        foreach ($divsStart as $div) {
            $description = "";
            $event = "";
            $divEvent = $div->find(".soccer__icon", 0);
            if ($divEvent) {
                $event = $this->getEvent($divEvent, true)['event'];
            }
            $descriptionDiv = $div->find(".soccer__comment", 0);
            if ($descriptionDiv) {
                $description = $descriptionDiv->text();
            }
            $arrTemp = [
                'description' => $description,
                'event' => $event,
                'time' => $div->find(".soccer__time", 0)->text(),
            ];
            $tracker[] = $arrTemp;
        }

        return $tracker;
    }
    public function crawlDetailStarts()
    {
        $start = [];
        $divCrawl = str_get_html($this->divStart);
        if (!$divCrawl) {
            return $start;
        }

        $divsStart = $divCrawl->find(".stat__row");
        foreach ($divsStart as $div) {
            $categorDiv = $div->find(".stat__categoryName", 0);
            $children = $categorDiv->children; // get an array of children
            foreach ($children as $child) {
                $child->outertext = ''; // This removes the element, but MAY NOT remove it from the original $myDiv
            }
            $categoryName = $categorDiv->innertext;
            $arrTemp = [
                'category' => $categoryName,
                'homeValue' => $div->find(".stat__homeValue", 0)->text(),
                'awayValue' => $div->find(".stat__awayValue", 0)->text(),
            ];
            $start[] = $arrTemp;
        }
        return $start;
    }

    public function getEvent($description, $is_comment = false)
    {
        $event = "";
        $strDescription = "";
        $svg = $description->find("div > div", 0);
        if (!$svg) {
            $svg = $description->find("div", 0);
            if (!$svg->find("title")) {
                $svg = $description->find("div", 1);
            }
        }
        //get event
        if ($svg) {
            if ($description->find("use", 0)) {
                $hrefIcon = $description->find("use", 0)->getAttribute("xlink:href");
                if (strpos($hrefIcon, "card") !== false) {
                    if (strpos($hrefIcon, "red-yellow-card") !== false) {
                        $event = "RedYellowCard";
                    } else {
                        // Get the value of the class attribute
                        $class_attr = $svg->getAttribute('class');
                        //  $classes = $svg->getAttribute('class');
                        if (strpos($class_attr, "redCard-ico") !== false) {
                            $event = "RedCard";
                        } else if (strpos($class_attr, "yellowCard-ico") !== false) {
                            $event = "YellowCard";
                        }
                        if (!$event) {
                            if ($svg->text() == "Red Card") {
                                $event = "RedCard";
                            } else {
                                $event = "YellowCard";
                            }
                        }
                    }
                } else {
                    $event = substr($hrefIcon, strpos($hrefIcon, "#") + 1);
                }
            } else {
                $class_attr = $svg->getAttribute('class');
                $class_attr = explode(" ", $class_attr)[0];
                $event = trim($class_attr);
            }
        }
        $strDescription = $description->getAttribute("title");
        if (!$strDescription) {
            $divDescription = $description->find('div', 0);
            if ($divDescription) {
                $strDescription = $divDescription->getAttribute("title");
            }
            if (!$strDescription) {
                $divDescription = $description->find(".card-ico > title", 0);
                if ($divDescription) {
                    $strDescription = $divDescription->text();
                }
            }
        }

        return [
            'event' => $event,
            'strDescription' => $strDescription,
        ];
    }
}