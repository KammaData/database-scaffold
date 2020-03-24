<?php

declare(strict_types=1);

namespace KammaData\DatabaseScaffold;

abstract class AbstractDatabaseClient {
    const USER="user";
    const PASSWD="passwd";
    const HOST="host";
    const PORT="port";
    const DATABASE="database";
    const CONNECT='connect';
    const LOCALHOST='localhost';

    protected $options;
    protected $dsn = '';
    protected $dbh = null;
    protected $defaults = [
        self::HOST => self::LOCALHOST,
        self::CONNECT => false
    ];

    public function __construct(array $options) {
        if (empty($options)) {
            throw new \ErrorException('Missing configuration options');
        }
        $this->options = array_merge($this->defaults, $options);
        $this->checkOptions($this->options);
        $this->dsn = $this->buildDSN();
        if ($this->options[self::CONNECT]) {
            $this->connect();
        }
    }

    public function __invoke(): object {
        return $this->getDBH();
    }

    public function getDSN(): string {
        return $this->dsn;
    }

    public function getDBH(): object {
        return $this->dbh;
    }

    public function connect(): void {
        $options = [
            \PDO::ATTR_PERSISTENT => true
        ];
        $user = null;
        $password = null;

        if (isset($this->options[self::USER]) && !empty($this->options[self::USER])) {
            $user = $this->options[self::USER];
        }

        if (isset($this->options[self::PASSWD]) && !empty($this->options[self::PASSWD])) {
            $password = $this->options[self::PASSWD];
        }

        $this->dbh = new \PDO($this->dsn, $user, $password, $options);
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    protected function checkOptions($options): void {
        foreach ($options as $key => $value) {
            $this->checkOption($key);
        }
    }

    protected function checkOption($option): void {
        if ($option !== self::PASSWD && (!isset($this->options[$option]))) {
            throw new \Exception(sprintf('Missing required %s option', $option));
        }
    }

    abstract protected function buildDSN(): string;
}

?>
