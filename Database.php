<?php


namespace FpDbTest;

use stdClass;
use Exception;
use mysqli;


class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        // Перепроверяем наличие соединения с базой данных
        if ($mysqli->connect_errno) {
            throw new Exception($mysqli->connect_error);
        }

        $this->mysqli = $mysqli;
    }

    // Метод для построения SQL-запроса на основе шаблона и аргументов
    public function buildQuery(string $query, array $args = []): string
    {
        if (empty($args)) {
            return $query;
        }

        $query = $this->bindParameters($query, $args);
        $query = $this->removeConditionalBlocks($query);
        $query = $this->removeConditionalBlocksBrackets($query);
        $query = $this->quoteQueryParameters($query);

        return $query;
    }

    // Метод для привязки аргументов к шаблону запроса
    private function bindParameters(string $query, array $args): string
    {
        $index = 0;
        return preg_replace_callback('/(\?\#|\?d|\?f|\?a|\?)/', function ($match) use ($args, &$index) {
            $this->checkParameterType($match[0], $args[$index]);
            return $this->format($args[$index++]);
        }, $query);
    }

    // Метод для удаления условных блоков из запроса
    private function removeConditionalBlocks(string $query): string
    {
        return preg_replace('/{[^}]*%skip:null%[^}]*}/', '', $query);
    }

    // Метод для экранирования значений параметров запроса
    private function quoteQueryParameters(string $query): string
    {
        return preg_replace('/=(\s*)`([^`]+)`/', "=\\1'$2'", $query);
    }

    // Метод для удаления фигурных скобок вокруг условных блоков
    private function removeConditionalBlocksBrackets(string $query): string
    {
        return preg_replace_callback("/(?<!')\{([^'}]+)(?<!')\}(?!')/", function ($match) {
            return $match[1];
        }, $query);
    }

    // Метод для проверки типа параметра
    private function checkParameterType(string $match, mixed $value): void
    {
        if ($match === '?' && is_array($value)) {
            throw new Exception('Incompatible parameter type');
        }
        if ($match === '?f' && (!is_float($value) && !is_null($value))) {
            throw new Exception('Incompatible parameter type');
        }
        if ($match === '?a' && !is_array($value)) {
            throw new Exception('Incompatible parameter type');
        }
        if ($match === '?d' && (!is_int($value) && !is_null($value) && !is_bool($value) && !is_object($value))) {
            throw new Exception('Incompatible parameter type');
        }
    }

    // Метод для форматирования значения параметра
    private function format(mixed $value): string
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return (string) $value;
        }

        if (is_object($value)) {
            return '%skip:null%';
        }

        if (is_string($value)) {
            return "`" . htmlspecialchars($value) . "`";
        }

        if (is_array($value)) {
            if (is_int(key($value))) {
                return implode(', ', array_map(static fn($element) => is_int($element) ? $element : "`$element`", $value));
            }

            return implode(', ', array_map(fn($key, $v) => "`$key` = " . $this->format($v), array_keys($value), $value));
        }

        throw new Exception('Undefined type');
    }

    public function skip(): object
    {
        return new stdClass();
    }
}