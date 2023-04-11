<?php

namespace Score\Repositories;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Phalcon\Mvc\User\Component;


class Selenium extends Component
{
    public $driver;
    public $cache; //cache to count driver;
    public $count;

    public function __construct($url)
    {
        // $this->cache = new CacheSelenium();
        // $count = $this->cache->getCache();
        // echo $count ."\r\n";
        // if (!$count) {
        //     $this->cache->setCache(1);
        // } else {
        //     $this->cache->setCache($count + 1);
        // }

        $ip = SELENIUM_END_POINT;

        //$ip = "13.250.21.188";
        $port = 4444;

        $connection = @fsockopen($ip, $port);


        if (is_resource($connection)) {
            echo 'Server is up and running.';
            fclose($connection);
        } else {
            echo 'Server is down.';
            die();
        }
        // exit;
        $host = "http://$ip:4444/wd/hub"; // URL của máy chủ Selenium
        $chromeOptions = new \Facebook\WebDriver\Chrome\ChromeOptions();
        // Bật chế độ headless
        $chromeOptions->addArguments(['--headless']);
        // Tắt hình ảnh
        $chromeOptions->addArguments(['--blink-settings=imagesEnabled=false']);
        // Tắt phông chữ
        $chromeOptions->addArguments(['--disable-extensions-file-access-check']);
        // Tắt video và âm thanh
        $chromeOptions->addArguments(['--disable-audio-output']);
        $this->driver = RemoteWebDriver::create($host, $chromeOptions->toCapabilities());

        // Lấy danh sách các handle tab
        $handles = $this->driver->getWindowHandles();

        // Đếm số lượng tab
        $tabCount = count($handles);

        // In ra số lượng tab đang mở
        echo "Số lượng tab đang mở: " . $tabCount;die();
        $time = microtime(true);
        $this->setURL($url);
        //   var_dump(microtime(true) - $time);
    }
    public function setURL($url = 'https://www.sofascore.com/football')
    {
        $this->driver->get($url);

        //  $this->driver->manage()->timeouts()->implicitlyWait(100); //to close tab
        //wait javascript load
        sleep(0.5);
        //return $this->driver->getWindowHandle();
    }
    public function waitItemHide($idDoom)
    {
        $wait = new WebDriverWait($this->driver, 1);
        $wait->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id($idDoom)
            )
        );
    }
    public function checkRam()
    {
        $jsonLength = strlen(json_encode($this->driver->executeScript('return window.performance.memory')));
        $jsonLength = strlen(json_encode($this->driver->executeScript('return window.performance.memory')));
        echo "Selenium RAM usage: " . $jsonLength . " bytes";
    }
    public function openTab()
    {
        $this->driver->executeScript("window.open()");
        $handles = $this->driver->getWindowHandles();
        $newHandle = end($handles);
        $this->driver->switchTo()->window($newHandle);
        return $this->driver;
    }
    public function swichTab($handle)
    {
        $this->driver->switchTo()->window($handle);
        return $this->driver->getWindowHandles();
    }
    public function countTab()
    {
        return count($this->driver->getWindowHandles());
    }
    public function getPageSource()
    {
        return $this->driver->getPageSource();
    }
    public function clickButton($domButton)
    {
        //$doomButton = 'button[data-tabid="mobileSportListType.true"]';
        $button = $this->driver->findElement(WebDriverBy::cssSelector($domButton));
        $button->click();
        sleep(2);
    }
    public function findElement($domElement)
    {
        //$domElement = 'div[aria-readonly="true"] > div >div ';
        $elements = $this->driver->findElement(WebDriverBy::cssSelector($domElement));
        return $elements;
    }
    public function findElements($domElement)
    {
        //$domElement = 'div[aria-readonly="true"] > div >div ';
        $elements = $this->driver->findElements(WebDriverBy::cssSelector($domElement));
        return $elements;
    }
    public function executeScript($script)
    {
        return $this->driver->executeScript($script);
    }
    public function quit()
    {
        // $count =  $this->cache->getCache();
        // $this->cache->setCache($count - 1);
        return $this->driver->quit();
    }
}
