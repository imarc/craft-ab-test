<?php

namespace imarc\abtest\elements;

use Craft;
use craft\base\Element;
use imarc\abtest\records\Option as OptionRecord;

class Option extends Element
{

    const OPTIONS_TABLE = '{{%abtest_options}}';

    public bool $hardDelete = true;

    public int $testId;
    public string $name;
    public string $handle;
    public int $weight;
    public string $innerHTML;

    public function rules(): array
    {
        return [
            [['name', 'handle', 'weight', 'innerHTML'], 'required'],
            [['handle'], 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/']
        ];
    }

    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert($this::OPTIONS_TABLE, $this->_getInsertData())
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update($this::OPTIONS_TABLE, $this->_getUpdateData(), ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    private function _getInsertData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'handle' => $this->handle,
            'weight' => $this->weight,
            'testId'=> $this->testId,
            'innerHTML' => $this->innerHTML
        ];
    }

    private function _getUpdateData(): array
    {
        if ($this->handle === 'default') {
            return [
                'weight' => $this->weight
            ];
        }

        return [
            'name' => $this->name,
            'handle' => $this->handle,
            'testId'=>$this->testId,
            'weight' => $this->weight,
            'innerHTML' => $this->innerHTML
        ];
    }
}
