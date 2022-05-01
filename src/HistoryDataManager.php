<?php
declare(strict_types=1);

namespace YaPro\HistoryData;

use PDO;
use UnexpectedValueException;
use YaPro\Helper\JsonHelper;
use function is_array;
use function is_string;

class HistoryDataManager
{
    private JsonHelper $jsonHelper;
    private PDO $pdo;

    public const TABLE_NAME = 'history_data';

    public function __construct(PDO $pdo, JsonHelper $jsonHelper)
    {
        $this->pdo = $pdo;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param string $operationName
     * @param string $entityName
     * @param string|int $entityId
     * @param string|array $json
     */
    public function add(string $operationName, string $entityName, $entityId, $json): void
    {
        if (is_array($json)) {
            $json = $this->jsonHelper->jsonEncode($json);
        }
        if (!is_string($json)) {
            throw new UnexpectedValueException('data must be json string');
        }
        $stmt = $this->pdo->prepare("INSERT INTO " . self::TABLE_NAME . " (
                ipAddress,
                userAgent,
                operationName,
                entityName,
                entityId,
                jsonData
            ) VALUES (
                :ipAddress,
                :userAgent,
                :operationName,
                :entityName,
                :entityId,
                :jsonData
            )");
        $stmt->execute([
            ':ipAddress' => $this->getIpAddress(),
            ':userAgent' => $this->getUserAgent(),
            ':operationName' => $operationName,
            ':entityName' => $entityName,
            ':entityId' => $entityId,
            ':jsonData' => $json,
        ]);
    }

    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'undefined';
    }

    // https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
    private function getIpAddress(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return 'undefined';
    }

    public static function getPdo(string $sqliteFilePath): PDO
    {
        return new PDO(
        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
            'sqlite:' . $sqliteFilePath,
            null,
            null,
            // https://www.php.net/manual/ru/pdo.setattribute.php
            [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // включить постоянные подключения:
                // PDO::ATTR_PERSISTENT => true,
            ]
        );
    }
}
