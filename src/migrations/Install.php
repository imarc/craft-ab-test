<?php

namespace imarc\abtest\migrations;

use Craft;
use craft\db\Migration;

class install extends Migration
{

    const TESTS_TABLE = '{{%abtest_tests}}';
    const OPTIONS_TABLE = '{{%abtest_options}}';

    public string $driver;

    public function safeUp(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->createTables();
        // if ($this->createTables()) {
        //     $this->createForeignKeys();

        // }

        return true;
    }

    public function safeDown(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    protected function createTables(): bool
    {

        $testTableCreated = false;
        $optionsTableCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema(self::TESTS_TABLE);
        if ($tableSchema === null) {
            $this->createTable(
                self::TESTS_TABLE,
                [
                    'id' => $this->primaryKey(),
                    'name' => $this->string()->notNull()->unique(),
                    'handle' => $this->string()->notNull()->unique(),
                    'startAt' => $this->dateTime()->null(),
                    'endAt' => $this->dateTime()->null(),
                    'enabled' => $this->boolean()->defaultValue(false),
                    'targetedUrls' => $this->json()->notNull(),
                    'targetedSelector' => $this->string()->notNull(),
                    'dateCreated' => $this->timestamp(),
                    'dateUpdated' => $this->timestamp()
                ]);
            $testTableCreated = true;
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema(self::OPTIONS_TABLE);
        if ($tableSchema === null) {
            $this->createTable(
                self::OPTIONS_TABLE,
                [
                    'id' => $this->primaryKey(),
                    'testId' => $this->bigInteger()->unsigned()->notNull(),
                    'name' => $this->string()->notNull(),
                    'handle' => $this->string()->notNull(),
                    'weight' => $this->smallInteger()->notNull(),
                    'innerHTML' => $this->longText(),
                    'dateCreated' => $this->timestamp(),
                    'dateUpdated' => $this->timestamp()
                ]);
            $optionsTableCreated = true;
        }

        return $testTableCreated && $optionsTableCreated;

    }

    // protected function createForeignKeys(): bool
    // {
    //     $this->addForeignKey(
    //         $this->db->getForeignKeyName(self::OPTIONS_TABLE, 'testId'),
    //         self::OPTIONS_TABLE,
    //         'testId',
    //         self::TESTS_TABLE,
    //         'id',
    //         'CASCADE',
    //         'CASCADE'

    //     );
    //     return true;

    // }

    protected function removeTables()
    {
        $this->dropTableIfExists(self::OPTIONS_TABLE);
        $this->dropTableIfExists(self::TESTS_TABLE);
    }


}
