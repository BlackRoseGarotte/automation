<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

final class ApplicationTest extends TestCase
{
    private Database $db;
    private string $tempPath;

    protected function setUp(): void
    {
        $this->tempPath = tempnam(sys_get_temp_dir(), 'phpunit_');
        $this->db = new Database($this->tempPath);

        $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
        $this->db->Execute($schema);
    }

    protected function tearDown(): void
    {
        unset($this->db);
        unlink($this->tempPath);
    }

    public function testDatabaseConnection(): void
    {
        $this->assertInstanceOf(Database::class, $this->db);
    }

    public function testExecute(): void
    {
        $this->db->Execute(
            "CREATE TABLE audit (id INTEGER PRIMARY KEY, note TEXT)"
        );

        $result = $this->db->Fetch(
            "SELECT name FROM sqlite_master
             WHERE type='table' AND name='audit'"
        );

        $this->assertCount(1, $result);
    }

    public function testFetch(): void
    {
        $rows = $this->db->Fetch(
            'SELECT title FROM page ORDER BY id'
        );

        $this->assertCount(3, $rows);
        $this->assertSame('Page 1', $rows[0]['title']);
    }

    public function testCount(): void
    {
        $this->assertSame(3, $this->db->Count('page'));
    }

    public function testCreate(): void
    {
        $id = $this->db->Create('page', [
            'title' => 'Created',
            'content' => 'Testing create'
        ]);

        $this->assertGreaterThan(3, $id);
        $this->assertSame(4, $this->db->Count('page'));
    }

    public function testRead(): void
    {
        $row = $this->db->Read('page', 1);

        $this->assertNotNull($row);
        $this->assertSame('Page 1', $row['title']);
    }

    public function testUpdate(): void
    {
        $affected = $this->db->Update('page', 1, [
            'title' => 'Updated'
        ]);

        $row = $this->db->Read('page', 1);

        $this->assertSame(1, $affected);
        $this->assertSame('Updated', $row['title']);
    }

    public function testDelete(): void
    {
        $affected = $this->db->Delete('page', 1);

        $this->assertSame(1, $affected);
        $this->assertNull($this->db->Read('page', 1));
        $this->assertSame(2, $this->db->Count('page'));
    }

    public function testPageConstructor(): void
    {
        $page = new Page(
            __DIR__ . '/../site/templates/index.tpl'
        );

        $this->assertInstanceOf(Page::class, $page);
    }

    public function testPageRender(): void
    {
        $page = new Page(
            __DIR__ . '/../site/templates/index.tpl'
        );

        $output = $page->Render([
            'title' => 'Test title',
            'content' => 'Hello Jenkins'
        ]);

        $this->assertStringContainsString(
            '<h1>Test title</h1>', $output
        );

        $this->assertStringContainsString(
            'Hello Jenkins', $output
        );
    }
}
