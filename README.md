# yapro/history-data

Run tests:
```shell
vendor/bin/phpunit tests/Functional/
```
## How to configure Symfony

Add to config/services.yaml
```yaml
    YaPro\Helper\:
      resource: '../vendor/yapro/helpers/src/*'

    YaPro\HistoryData\:
        resource: '../vendor/yapro/history-data/src/*'

    yapro.pdo.sqlite:
      class: YaPro\HistoryData\HistoryDataManager
      factory: ['YaPro\HistoryData\HistoryDataManager', 'getPdo']
      arguments: ['%env(FILE_PATH_TO_SQLITE_DB)%',]

    YaPro\HistoryData\HistoryDataManager:
      arguments: ['@yapro.pdo.sqlite',]
```

By default, we use mydb.sq3 file. If you want to have own sqlite db - make it:
```shell
touch $(pwd)/mydb.sq3
```

And make the table in sqlite db:
```sqlite
create table history_data
(
    createdAt INTEGER default CURRENT_TIMESTAMP not null,
    ipAddress TEXT not null,
    userAgent TEXT not null,
    operationName TEXT not null,
    entityName TEXT not null,
    entityId TEXT not null,
    jsonData TEXT not null
);
```
And don`t forgеt, almost every sqlite table has a hidden AUTOINCREMENT column [rowid](https://www.sqlite.org/autoinc.html):
```sql92
SELECT rowid, * FROM history_data
```
