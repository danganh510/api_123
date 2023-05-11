<?php

/**
 * Services are globally registered in this file
 */

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\Router;
use Phalcon\DI\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Loader;
use Phalcon\Mvc\Model\Manager;


if (!defined('MyS3Key')) {
    define('MyS3Key', 'AKIAZPULICYWP6EU5M4F');
}
if (!defined('MyS3Secret')) {
    define('MyS3Secret', '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');
}

if (!defined('MyS3Bucket')) {
    define('MyS3Bucket', 'Score');
}
if (!defined('MyCloudFrontURL')) {
    define('MyCloudFrontURL', 'https://dovyy1zxit6rl.cloudfront.net/');
}
include __DIR__ . '/../vendor/autoload.php';

/**
 * Read configuration
 */
$config = include __DIR__ . "/config.php";

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader = new Loader();
$loader->registerNamespaces(array(
    'General\Models' => __DIR__ . '/../apps/models/general/',
    'Score\Models' => __DIR__ . '/../apps/models/',
    'Score\Repositories' => [
        __DIR__ . '/../apps/repositories/',
        __DIR__ . '/../apps/repositories/Crawler/',
        __DIR__ . '/../apps/repositories/CrawlerList/',
        __DIR__ . '/../apps/repositories/CrawlerDetail/',
        __DIR__ . '/../apps/repositories/Cache/',
    ],
    'Score\Utils' => __DIR__ . '/../apps/library/Utils/'
));

$loader->registerDirs(
    array(
        __DIR__ . '/../apps/library/',
        __DIR__ . '/../apps/library/SMTP/'

    )
);
$loader->register();

/**
 * Cloud Flare Fix CUSTOMER IP
 */
function ip_in_range($ip, $range)
{
    if (strpos($range, '/') !== false) {
        // $range is in IP/NETMASK format
        list($range, $netmask) = explode('/', $range, 2);
        if (strpos($netmask, '.') !== false) {
            // $netmask is a 255.255.0.0 format
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
        } else {
            // $netmask is a CIDR size block
            // fix the range argument
            $x = explode('.', $range);
            while (count($x) < 4) $x[] = '0';
            list($a, $b, $c, $d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);

            # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
            #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

            # Strategy 2 - Use math to create it
            $wildcard_dec = pow(2, (32 - $netmask)) - 1;
            $netmask_dec = ~$wildcard_dec;

            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }
    } else {
        // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
        if (strpos($range, '*') !== false) { // a.b.*.* format
            // Just convert to A-B format by setting * to 0 for A and 255 for B
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }

        if (strpos($range, '-') !== false) { // A-B format
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u", ip2long($lower));
            $upper_dec = (float)sprintf("%u", ip2long($upper));
            $ip_dec = (float)sprintf("%u", ip2long($ip));
            return (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec));
        }
        return false;
    }
}

if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $cf_ip_ranges = array(
        '204.93.240.0/24',
        '204.93.177.0/24',
        '199.27.128.0/21',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/12',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2c0f:f248::/32',
        '2a06:98c0::/29'
    );
    foreach ($cf_ip_ranges as $range) {
        if (ip_in_range($_SERVER['REMOTE_ADDR'], $range)) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            break;
        }
    }
}

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di['db'] = function () use ($config) {
    return new DbAdapter(array(
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->name,
        "schema" => $config->database->name,
        'charset' => $config->database->charset
    ));
};




/**
 * Registering a router
 */

$di['router'] = function () {
    $router = new Router(false);
    $router->removeExtraSlashes(true);
    $router->setDefaultModule("backend");

    //Set 404 paths
    $router->notFound(array(
        "module" => "backend",
        "controller" => "notfound",
        "action" => "index"
    ));

    $router->add("/", array(
        "module" => "backend",
        "controller" => "index",
        "action" => "index"
    ));
    $router->add("/crawler", array(
        "module" => "backend",
        "controller" => "crawler",
        "action" => "index"
    ));
    $router->add("/crawler-structure", array(
        "module" => "backend",
        "controller" => "crawlerstructure",
        "action" => "index"
    ));
    $router->add("/crawler-tour", array(
        "module" => "backend",
        "controller" => "crawlertour",
        "action" => "index"
    ));
    $router->add("/crawler-tour-v2", array(
        "module" => "backend",
        "controller" => "crawlertourv2",
        "action" => "index"
    ));
    $router->add("/crawler-detail", array(
        "module" => "backend",
        "controller" => "crawlerdetail",
        "action" => "index"
    ));
    $router->add("/crawler-detail-live", array(
        "module" => "backend",
        "controller" => "crawlerdetaillive",
        "action" => "index"
    ));
    $router->add("/crawler-logo-small", array(
        "module" => "backend",
        "controller" => "crawlimage",
        "action" => "index"
    ));
    $router->add("/crawler-logo-medium", array(
        "module" => "backend",
        "controller" => "crawlimage",
        "action" => "logomedium"
    ));
    $router->add("/dashboard/logout", array(
        "module" => "backend",
        "controller" => "login",
        "action" => "logout"
    ));
    $router->add("/dashboard/login", array(
        "module" => "backend",
        "controller" => "login",
        "action" => "index"
    ));
    $router->add("/dashboard/accessdenied", array(
        "module" => "backend",
        "controller" => "index",
        "action" => "accessdenied"
    ));

    //   Page Controller
    $router->add('/dashboard/list-page', array(
        "module" => "backend",
        "controller" => "page",
        "action" => "index"
    ));
    $router->add('/dashboard/create-page', array(
        "module" => "backend",
        "controller" => "page",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-page', array(
        "module" => "backend",
        "controller" => "page",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-page', array(
        "module" => "backend",
        "controller" => "page",
        "action" => "delete"
    ));

    //   article Controller
    $router->add('/dashboard/list-article', array(
        "module" => "backend",
        "controller" => "article",
        "action" => "index"
    ));
    $router->add('/dashboard/create-article', array(
        "module" => "backend",
        "controller" => "article",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-article', array(
        "module" => "backend",
        "controller" => "article",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-article', array(
        "module" => "backend",
        "controller" => "article",
        "action" => "delete"
    ));

    // Role Controller
    $router->add("/dashboard/list-role", array(
        "module" => "backend",
        "controller" => "role",
        "action" => "index"
    ));
    $router->add("/dashboard/create-role", array(
        "module" => "backend",
        "controller" => "role",
        "action" => "create"
    ));
    $router->add("/dashboard/edit-role", array(
        "module" => "backend",
        "controller" => "role",
        "action" => "edit"
    ));
    $router->add("/dashboard/delete-role", array(
        "module" => "backend",
        "controller" => "role",
        "action" => "delete"
    ));

    // Language Controller
    $router->add('/dashboard/list-language', array(
        "module" => "backend",
        "controller" => 'language',
        "action" => "index"
    ));
    $router->add('/dashboard/create-language', array(
        "module" => "backend",
        "controller" => 'language',
        "action" => "create"
    ));
    $router->add('/dashboard/edit-language', array(
        "module" => "backend",
        "controller" => 'language',
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-language', array(
        "module" => "backend",
        "controller" => 'language',
        "action" => "delete"
    ));

    // User controller
    $router->add('/dashboard/list-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "index"
    ));
    $router->add('/dashboard/create-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "delete"
    ));
    $router->add('/dashboard/password-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "password"
    ));
    $router->add('/dashboard/information-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "information"
    ));
    $router->add('/dashboard/role-user', array(
        "module" => "backend",
        "controller" => "user",
        "action" => "role"
    ));

    // Upload File Controller
    $router->add('/dashboard/list-cloudupload', array(
        "module" => "backend",
        "controller" => 'cloudupload',
        "action" => 'index'
    ));
    $router->add('/dashboard/dashboard/form-upload', array(
        "module" => "backend",
        "controller" => 'formupload',
        "action" => 'index'
    ));
    // Config Controller
    $router->add('/dashboard/list-configcontent', array(
        "module" => "backend",
        "controller" => "configcontent",
        "action" => "index"
    ));
    $router->add('/dashboard/create-configcontent', array(
        "module" => "backend",
        "controller" => "configcontent",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-configcontent', array(
        "module" => "backend",
        "controller" => "configcontent",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-configcontent', array(
        "module" => "backend",
        "controller" => "configcontent",
        "action" => "delete"
    ));

    // Type Controller
    $router->add('/dashboard/list-type', array(
        "module" => "backend",
        "controller" => "type",
        "action" => "index"
    ));
    $router->add('/dashboard/create-type', array(
        "module" => "backend",
        "controller" => "type",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-type', array(
        "module" => "backend",
        "controller" => "type",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-type', array(
        "module" => "backend",
        "controller" => "type",
        "action" => "delete"
    ));


    //    ------------Match Controller
    $router->add('/dashboard/list-match', array(
        "module" => "backend",
        "controller" => "match",
        "action" => "index"
    ));
    $router->add('/dashboard/create-match', array(
        "module" => "backend",
        "controller" => "match",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-match', array(
        "module" => "backend",
        "controller" => "match",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-match', array(
        "module" => "backend",
        "controller" => "match",
        "action" => "delete"
    ));

    //    ------------Tournament Controller
    $router->add('/dashboard/list-tournament', array(
        "module" => "backend",
        "controller" => "tournament",
        "action" => "index"
    ));
    $router->add('/dashboard/create-tournament', array(
        "module" => "backend",
        "controller" => "tournament",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-tournament', array(
        "module" => "backend",
        "controller" => "tournament",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-tournament', array(
        "module" => "backend",
        "controller" => "tournament",
        "action" => "delete"
    ));
    //    ------------Team Controller
    $router->add('/dashboard/list-team', array(
        "module" => "backend",
        "controller" => "team",
        "action" => "index"
    ));
    $router->add('/dashboard/create-team', array(
        "module" => "backend",
        "controller" => "team",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-team', array(
        "module" => "backend",
        "controller" => "team",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-team', array(
        "module" => "backend",
        "controller" => "team",
        "action" => "delete"
    ));


    //    ------------Banner Controller
    $router->add('/dashboard/list-banner', array(
        "module" => "backend",
        "controller" => "banner",
        "action" => "index"
    ));
    $router->add('/dashboard/create-banner', array(
        "module" => "backend",
        "controller" => "banner",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-banner', array(
        "module" => "backend",
        "controller" => "banner",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-banner', array(
        "module" => "backend",
        "controller" => "banner",
        "action" => "delete"
    ));

    // Country Controller
    $router->add('/dashboard/list-country', array(
        "module" => "backend",
        "controller" => "country",
        "action" => "index"
    ));
    $router->add('/dashboard/create-country', array(
        "module" => "backend",
        "controller" => "country",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-country', array(
        "module" => "backend",
        "controller" => "country",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-country', array(
        "module" => "backend",
        "controller" => "country",
        "action" => "delete"
    ));
    // Country Controller
    $router->add('/dashboard/list-area', array(
        "module" => "backend",
        "controller" => "area",
        "action" => "index"
    ));
    $router->add('/dashboard/create-area', array(
        "module" => "backend",
        "controller" => "area",
        "action" => "create"
    ));
    $router->add('/dashboard/edit-area', array(
        "module" => "backend",
        "controller" => "area",
        "action" => "edit"
    ));
    $router->add('/dashboard/delete-area', array(
        "module" => "backend",
        "controller" => "area",
        "action" => "delete"
    ));
    $router->add('/dashboard/list-contactus', array(
        "module" => "backend",
        "controller" => "contactus",
        "action" => "index"
    ));
    $router->add('/dashboard/view-contactus', array(
        "module" => "backend",
        "controller" => "contactus",
        "action" => "view"
    ));
    $router->add("/list-deletecachetool", array(
        "module" => "backend",
        "controller" => "deletecachetool",
        "action" => "index"
    ));
    $router->add("/dashboard/delete-all-cache", array(
        "module" => "backend",
        "controller" => "deletecachetool",
        "action" => "deleteallcache"
    ));
    $router->add("/auto-update-time", array(
        "module" => "backend",
        "controller" => "updatetime",
        "action" => "index"
    ));
    

    //API CACHE
    $router->add('/cache-match-live', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "cachematchlive"
    ));
    $router->add('/cache-match-7-day', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "cachematch7day"
    ));
    $router->add('/get-team', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "getcacheteam"
    ));
    $router->add('/get-tour', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "getcachetournament"
    ));
    $router->add('/delete-cache-data', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "deletecache"
    ));

    //API
    $router->add('/get-tour', array(
        "module" => "api",
        "controller" => 'tournament',
        "action" => "gettounamentshow"
    ));
    $router->add('/get-standing-tour', array(
        "module" => "api",
        "controller" => 'tournament',
        "action" => "getstandingstour"
    ));
    $router->add('/get-match-tour-show', array(
        "module" => "api",
        "controller" => 'tournament',
        "action" => "getscheduletourshow"
    ));
    $router->add('/get-match-by-tour', array(
        "module" => "api",
        "controller" => 'tournament',
        "action" => "getscheduletour"
    ));
    $router->add('/save-match', array(
        "module" => "api",
        "controller" => 'savematch',
        "action" => "list"
    ));
    $router->add('/create-cache-match', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "match"
    ));
    $router->add('/create-cache-live', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "live"
    ));
    $router->add('/create-cache-team', array(
        "module" => "api",
        "controller" => 'cache',
        "action" => "team"
    ));
    $router->add('/get-config', array(
        "module" => "api",
        "controller" => 'getconfig',
        "action" => "list"
    ));
    $router->add('/get-table-{table}', array(
        "module" => "api",
        "controller" => 'getdata',
        "action" => "list"
    ));
    $router->add('/get-detail-{table}', array(
        "module" => "api",
        "controller" => 'getdata',
        "action" => "detail"
    ));
    $router->add('/get-list-match', array(
        "module" => "api",
        "controller" => 'match',
        "action" => "list"
    ));
    $router->add('/get-list-match-test', array(
        "module" => "api",
        "controller" => 'matchtest',
        "action" => "list"
    ));

    $router->add('/get-match', array(
        "module" => "api",
        "controller" => 'match',
        "action" => "detail"
    ));

    $router->handle();
    return $router;
};

/**
 * Start the session the first time some component request the session service
 */
$di['session'] = function () {
    $session = new SessionAdapter();
    $session->start();
    return $session;
};
/**
 * Register My component
 */
$di->set('my', function () {
    return new \My();
});

/**
 * Register GlobalVariable component
 */
$di->set('globalVariable', function () {
    return new \GlobalVariable();
});

/**
 * Register cookie
 */
$di->set('cookies', function () {
    $cookies = new \Phalcon\Http\Response\Cookies();
    $cookies->useEncryption(false);
    return $cookies;
}, true);

/**
 * Register key for cookie encryption
 */
$di->set('crypt', function () {
    $crypt = new \Phalcon\Crypt();
    $crypt->setKey('binmedia123@@##'); //Use your own key!
    return $crypt;
});

/**
 * Register models manager
 */
$di->set('modelsManager', function () {
    return new Manager();
});

/**
 * Register PHPMailer manager
 */
// $di->set('myMailer', function () {
//     require_once(__DIR__ . "/../apps/library/SMTP/class.phpmailer.php");
//     $mail = new \PHPMailer();
//     $mail->IsSMTP();//telling the class to use SMTP
//     $mail->SMTPAuth = true;
//     $mail->SMTPSecure = "tls";
//     $mail->Host = "email-smtp.us-west-2.amazonaws.com";
//     $mail->Username = "AKIAZPULICYWJ7UI6GUZ";
//     $mail->Password = "BNr0S+jBNdlDm0RtmJIIN8L1dFia0Y5r1R9Sk6Xg2a9r";
//     $mail->CharSet = 'utf-8';
//     return $mail;
// });
