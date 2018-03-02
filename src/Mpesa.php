<?php

namespace Kabangi\Mpesa\Laravel;

use Kabangi\Mpesa\Engine\Core;
use Kabangi\Mpesa\Laravel\Cache;
use Kabangi\Mpesa\Laravel\Config;
use Kabangi\Mpesa\Engine\MpesaTrait;
use Kabangi\Mpesa\Auth\Authenticator;
use Kabangi\Mpesa\Engine\CurlRequest;

/**
 * Class Mpesa
 *
 * @category PHP
 *
 * @author   Julius Kabangi <Kabangijulius@gmail.com>
 */
class Mpesa
{
    use MpesaTrait;
    /**
     * @var Mpesa
     */
    private $engine;

    /**
     * Mpesa constructor.
     *
     */
    public function __construct($myconfig = []){
        $config = new Config($myconfig);
        $cache = new Cache($config);
        $auth = new Authenticator();
        $httpClient = new CurlRequest();
        $this->engine = new Core($config, $cache,$httpClient,$auth);
    }
}
