<?php

namespace Scrimmy\Weather\Service;

use Symfony\Component\Dotenv\Dotenv;

class DatabaseService extends \PDO
{
    public function __construct()
    {
        (new Dotenv())->load('.env');

        parent::__construct($_ENV['DB_CONNECTION'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    }
}