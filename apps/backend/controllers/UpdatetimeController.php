<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Language;
use Score\Models\ScLanguage;
use Score\Models\ScType;
use Score\Models\ScTypeLang;
use Score\Repositories\Article;
use Score\Repositories\Country;
use Score\Repositories\Type;
use Score\Repositories\TypeLang;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\NativeArray;
use Score\Models\ScMatch;
use Score\Repositories\CacheMatchLive;

class UpdatetimeController extends ControllerBase
{
    public function indexAction()
    {
        $start_time_cron = time() + 0 * 24 * 60 * 60;
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";

        $time_end = time() + 3 * 60;
        $time_begin = time() - 3 * 60;
        $time_now = time();
        $arrMatch = ScMatch::find(
            "match_status = 'S' OR (match_status = 'F' AND match_time_finish < $time_end  AND match_time_finish > $time_now) OR (match_status = 'W' AND match_start_time > $time_begin AND match_start_time < $time_now) "
        );

        $arrMatchNew = [];
        foreach ($arrMatch as $match) {
            if (is_numeric($match->getMatchTime()) && $match->getMatchStatus() == "S") {
                if (time() - $match->getMatchStartTime() > $match->getMatchTime() * 60) {
                    var_dump(time(), $match->getMatchStartTime(),$match->getMatchTime());
                    $match->setMatchTime($match->getMatchTime() + 1);
                    $match->save();
                }

            }
            $arrMatchNew[] = $match->toArray();
        }
        $matchCache = new CacheMatchLive();
        $matchCache->setCache(json_encode($arrMatchNew));
        echo "---finish in " . (time() - $start_time_cron) . " second \n\r";
        die();
    }
}