<?php

declare(strict_types=1);

namespace KammaData\DatabaseScaffold\PostgreSQL;

use KammaData\DatabaseScaffold\AbstractDatabaseClient;

class PostgreSQLClient extends AbstractDatabaseClient {
    const POSTGRES_PORT = 5432;

    protected $defaults = [
        self::HOST => self::LOCALHOST,
        self::PORT => self::POSTGRES_PORT
    ];

    public function __construct(array $options) {
        $this->options = array_merge($this->defaults, $options);
        parent::__construct($options);
    }

    public function parseNativeArray($source, $start=0, &$end=NULL): ?array {
        if (empty($source) || $source[0] != '{') return NULL;

        $return = [];
        $string = false;
        $quote = '';
        $len = strlen($source);
        $v = '';

        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $source[$i];

            if (!$string && $ch == '}') {
                if ($v !== '' || !empty($return)) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            }

            elseif (!$string && $ch == '{') {
                $v = pg_array_parse($source, $i, $i);
            }

            elseif (!$string && $ch == ','){
                $return[] = $v;
                $v = '';
            }

            elseif (!$string && ($ch == '"' || $ch == "'")) {
                $string = true;
                $quote = $ch;
            }

            elseif ($string && $ch == $quote && $source[$i - 1] == "\\") {
                $v = substr($v, 0, -1) . $ch;
            }

            elseif ($string && $ch == $quote && $source[$i - 1] != "\\") {
                $string = false;
            }

            else {
                $v .= $ch;
            }
        }

        return $return;
    }

    public function encodeNativeStringArray(array $source): string {
        if (empty($source)) {
            return 'null';
        }
        return sprintf('ARRAY [%s]', sprintf("'%s'", implode("','", $source)));
    }

    public function encodeNativeJSON(array $source): string {
        return sprintf('\'%s\'', json_encode($source));
    }

    protected function buildDSN(): string {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s',
            $this->options[self::HOST],
            $this->options[self::PORT],
            $this->options[self::DATABASE]
        );
        return $dsn;
    }
}

?>
