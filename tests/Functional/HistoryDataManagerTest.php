<?php
declare(strict_types=1);

namespace YaPro\HistoryData\Tests\Functional;

use PDO;
use PHPUnit\Framework\TestCase;
use YaPro\HistoryData\HistoryDataManager;
use YaPro\Helper\JsonHelper;

class HistoryDataManagerTest extends TestCase
{
    private static HistoryDataManager $historyDataManager;
    private static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = HistoryDataManager::getPdo(__DIR__ . '/../../mydb.sq3');
        self::$historyDataManager = new HistoryDataManager(self::$pdo, new JsonHelper());
    }

    public function testCreate()
    {
        self::$pdo->exec('DELETE FROM ' . HistoryDataManager::TABLE_NAME . ';');

        $json = '{"title": "title1", "comments": [{"message": "str1"}, {"message": "str2"}]}';
        self::$historyDataManager->add('insert', 'table_name', $json);

        $sth = self::$pdo->prepare("SELECT * FROM " . HistoryDataManager::TABLE_NAME);
        $sth->execute();
        $result = $sth->fetchAll()[0];
        unset($result['createdAt']);

        $this->assertEquals($result, [
            'ipAddress' => 'undefined',
            'userAgent' => 'undefined',
            'operationName' => 'insert',
            'tableName' => 'table_name',
            'jsonData' => $json,
        ]);
    }
}
