<?php

namespace imarc\abtest\elements;

use craft\elements\db\ElementQuery;

class ABTestQuery extends ElementQuery
{

    const TESTS_TABLE = 'abtest_tests';

    protected function beforePrepare(): bool
    {
        $this->joinElementTable(self::TESTS_TABLE);

        $this->query->select([
            'abtest_tests.id',
            'abtest_tests.name',
            'abtest_tests.handle',
            'abtest_tests.startAt',
            'abtest_tests.endAt',
            'abtest_tests.enabled',
            'abtest_tests.targetedUrls',
            'abtest_tests.targetedSelector'
        ]);

        return parent::beforePrepare();
    }
}


