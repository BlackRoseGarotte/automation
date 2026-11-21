<?php

declare(strict_types=1);

function message(string $type, string $message): void
{
    echo date('Y-m-d H:i:s') . " [{$type}] {$message}" . PHP_EOL;
}

function info(string $message): void { message('INFO', $message); }
function error(string $message): void { message('ERROR', $message); }

function assertExpression(bool $expression, string $pass = 'Pass', string $fail = 'Fail'): bool
{
    $expression ? info($pass) : error($fail);
    return $expression;
}

class TestFramework
{
    private array $tests = [];
    private int $success = 0;

    public function add(string $name, callable $test): void
    {
        $this->tests[$name] = $test;
    }

    public function run(): void
    {
        foreach ($this->tests as $name => $test) {
            info("Running test {$name}");
            try {
                if ($test() === true) {
                    $this->success++;
                } else {
                    error("Test {$name} failed");
                }
            } catch (Throwable $exception) {
                error("Test {$name} threw " . get_class($exception) . ': ' . $exception->getMessage());
            }
            info("End test {$name}");
        }
    }

    public function getResult(): string
    {
        return "{$this->success} / " . count($this->tests);
    }

    public function allPassed(): bool
    {
        return $this->success === count($this->tests) && count($this->tests) > 0;
    }
}
