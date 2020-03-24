<?php

declare(strict_types=1);

namespace KammaData\DatabaseScaffold\MySQL;

class MySQLClient extends AbstractDatabaseClient {
    const MYSQL_PORT = 3306;

    protected $defaults = [
        self::HOST => self::LOCALHOST,
        self::PORT => self::MYSQL_PORT
    ];

    public function __construct(array $options) {
        $this->options = array_merge($this->defaults, $options);
        parent::__construct($options);
    }

    protected function buildDSN(): string {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s',
            $this->options[self::HOST],
            $this->options[self::PORT],
            $this->options[self::DATABASE]
        );
        return $dsn;
    }
}

?>
