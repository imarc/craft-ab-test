<?php

namespace imarc\abtest\models;

use craft\base\Model;

class Settings extends Model
{

    public $googleAnalyticsId;

    public function rules(): array
    {
        return [
            ['googleAnalyticsId', 'string']
        ];
    }
}
