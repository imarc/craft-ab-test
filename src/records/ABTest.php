<?php

namespace imarc\abtest\records;

use craft\db\ActiveRecord;
use craft\db\ActiveQuery;

class ABTest extends ActiveRecord
{

    public static function tableName() {
        return '{{%abtest_tests}}';
    }

    public function getOptions(): ActiveQuery
    {
        return $this->hasMany(Option::class, ['testId' => 'id']);
    }

    public function getAllOptionsAsArray(): array
    {
        $returnArray = [];

        $options = $this->getOptions()->all();
        foreach ($options as $opt) {
            $returnArray[] = $opt->toArray();
        }

        return $returnArray;
    }

}
