<?php

declare(strict_types=1);

require_once __DIR__ . '/testframework.php';
require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

// Each CI run uses an isolated temporary SQLite database.
$tempPath = tempnam(sys_get_temp_dir(), 'jenkins_sqlite_');
if ($tempPath === false) {
    fwrite(STDERR, "Could not create temporary database\n");
    exit(1);
}

try {
    $db = new Database($tempPath);
    $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
    if ($schema === false) { throw new RuntimeException('Missing schema'); }
    $db->Execute($schema);
    $tests = new TestFramework();

    $tests->add('Database connection', fn() => assertExpression(
        $db instanceof Database, 'Connected to SQLite', 'Could not connect'
    ));
    $tests->add('Execute method', function () use ($db): bool {
        $result = $db->Execute("CREATE TABLE audit (id INTEGER PRIMARY KEY, note TEXT)");
        return assertExpression($result === 0, 'Execute works', 'Execute failed');
    });
    $tests->add('Fetch method', function () use ($db): bool {
        $rows = $db->Fetch('SELECT title FROM page ORDER BY id');
        return assertExpression(count($rows) === 3 && $rows[0]['title'] === 'Page 1', 'Fetch works', 'Fetch failed');
    });
    $tests->add('Count method', fn() => assertExpression(
        $db->Count('page') === 3, 'Count works', 'Count failed'
    ));
    $createdId = null;
    $tests->add('Create method', function () use ($db, &$createdId): bool {
        $createdId = $db->Create('page', ['title' => 'Created', 'content' => 'Testing create']);
        return assertExpression($createdId > 3 && $db->Count('page') === 4, 'Create works', 'Create failed');
    });
    $tests->add('Read method', function () use ($db, &$createdId): bool {
        $row = $db->Read('page', (int)$createdId);
        return assertExpression($row !== null && $row['title'] === 'Created', 'Read works', 'Read failed');
    });
    $tests->add('Update method', function () use ($db, &$createdId): bool {
        $affected = $db->Update('page', (int)$createdId, ['title' => 'Updated']);
        $row = $db->Read('page', (int)$createdId);
        return assertExpression($affected === 1 && $row['title'] === 'Updated', 'Update works', 'Update failed');
    });
    $tests->add('Delete method', function () use ($db, &$createdId): bool {
        $affected = $db->Delete('page', (int)$createdId);
        return assertExpression($affected === 1 && $db->Read('page', (int)$createdId) === null && $db->Count('page') === 3, 'Delete works', 'Delete failed');
    });
    $tests->add('Read missing record', fn() => assertExpression(
        $db->Read('page', 99999) === null, 'Missing record handled', 'Missing record failed'
    ));
    $tests->add('Reject invalid table name', function () use ($db): bool {
        try { $db->Count('page; DROP TABLE page'); }
        catch (InvalidArgumentException $exception) { return assertExpression(true, 'Unsafe table name rejected'); }
        return assertExpression(false, 'Unexpected success', 'Unsafe table name accepted');
    });
    $tests->add('Page constructor', function (): bool {
        $page = new Page(__DIR__ . '/../site/templates/index.tpl');
        return assertExpression($page instanceof Page, 'Page constructed', 'Page construction failed');
    });
    $tests->add('Page Render method', function (): bool {
        $page = new Page(__DIR__ . '/../site/templates/index.tpl');
        $output = $page->Render(['title' => 'Test title', 'content' => 'Hello Jenkins']);
        return assertExpression(str_contains($output, '<h1>Test title</h1>') && str_contains($output, 'Hello Jenkins'), 'Render works', 'Render failed');
    });
    $tests->add('Page Render escapes HTML', function (): bool {
        $page = new Page(__DIR__ . '/../site/templates/index.tpl');
        $output = $page->Render(['title' => '<script>alert(1)</script>', 'content' => 'Safe']);
        return assertExpression(!str_contains($output, '<script>') && str_contains($output, '&lt;script&gt;'), 'HTML escaped', 'HTML not escaped');
    });
    $tests->add('Page rejects missing template', function (): bool {
        try { new Page(__DIR__ . '/does-not-exist.tpl'); }
        catch (InvalidArgumentException $exception) { return assertExpression(true, 'Missing template rejected'); }
        return assertExpression(false, 'Unexpected success', 'Missing template accepted');
    });

    $tests->run();
    echo PHP_EOL . 'RESULT: ' . $tests->getResult() . PHP_EOL;
    exit($tests->allPassed() ? 0 : 1);
} finally {
    @unlink($tempPath);
}
