<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.03.2015
 */
namespace skeeks\cms\components\db;
use skeeks\cms\helpers\db\DbDsnHelper;
use skeeks\sx\Dir;
use yii\base\Component;
use yii\db\Connection;

/**
 * @property Dir    $backupDir
 *
 *
 * Class DbDumpComponent
 * @package skeeks\cms\components\db
 */
class DbDumpComponent extends Component
{
    public $backupDirPath;
    public $dbConnectionName        = "db";

    /**
     * @var Connection
     */
    public $connection;

    public function init()
    {
        parent::init();

        if (!$this->backupDirPath)
        {
            $this->backupDirPath = BACKUP_DIR . "/db";
        }

        /**
         * TODO: добавить проверки
         */
        $this->connection = \Yii::$app->{$this->dbConnectionName};

        if (!$this->connection || !$this->connection instanceof Connection)
        {
            throw new \InvalidArgumentException("Некорректный коннект к базе данных");
        }
    }

    /**
     * @return Dir
     */
    public function getBackupDir()
    {
        return new Dir($this->backupDirPath);
    }


    /**
     *
     */
    public function dumpRun()
    {
        if (!$this->backupDir->isExist())
        {
            $this->backupDir->make();
        }

        if (!$this->backupDir->isExist())
        {
            throw new \InvalidArgumentException("Не получилось создать папку с файлами бекапов: " . $this->backupDir->getPath());
        }

        $dsn = new DbDsnHelper($this->connection);

        $file       = $this->backupDir->newFile($dsn->dbname . "__" . date('Y-m-d_H:i:s') . ".sql");
        $filePath   = $file->getPath();

        $cmd = "mysqldump -h{$dsn->host} -u {$dsn->username} -p{$dsn->password} {$dsn->dbname} > {$filePath}";

        system($cmd);
    }

    /**
     * @return string
     */
    public function dumpRestore($fileName)
    {
        if (!$this->backupDir->isExist())
        {
            throw new \InvalidArgumentException("Бэкап файлов не найдено" . $this->backupDir->getPath());
        }

        $file = $this->backupDir->newFile($fileName);
        if (!$file->isExist())
        {
            throw new \InvalidArgumentException("Бэкап файл не найден" . $file->getPath());
        }

        $filePath = $file->getPath();

        $dsn = new DbDsnHelper($this->connection);
        $cmd = "mysql -h{$dsn->host} -u{$dsn->username} -p{$dsn->password} {$dsn->dbname} < {$filePath}";

        echo $cmd;
        system($cmd);
    }
}
