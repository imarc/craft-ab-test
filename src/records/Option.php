<?php

namespace imarc\abtest\records;

use craft\db\ActiveRecord;

class Option extends ActiveRecord
{

    public static function tableName() {
        return '{{%abtest_options}}';
    }

}
