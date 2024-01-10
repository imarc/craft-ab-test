<?php

namespace imarc\abtest\variables;

use Craft;
use craft\helpers\Template as TemplateHelper;
use imarc\abtest\records\ABTest as TestRecord;
use Twig\Markup;

class ABTestVariable
{

    public function testVariable(): Markup
    {
        return TemplateHelper::raw("<h1>Test Variable</h1>");
    }

    public function abTestScript(): Markup
    {
        $currentUrl = Craft::$app->request->getAbsoluteUrl();

        $tests = TestRecord::find()
            ->where("JSON_SEARCH(targetedUrls, 'all', '%" . str_replace(str_replace($currentUrl, "/", "\/"), "*", "%") . "%') is not null")
            ->andWhere("now() >= startAt or startAt is null")
            ->andWhere("(now() <= endAt or endAt is null)")
            ->andWhere("enabled = true")
            ->all();

        $returnTests = [];

        foreach ($tests as $test) {
            $options = $test->getAllOptionsAsArray();
            $returnTests[] = [
                "test" => $test->toArray(),
                "options" => $options
            ];
        }


        $testString =  json_encode($returnTests);

        //$distUrl = Craft::$app->assetManager->getPublishedUrl('@imarc/abtest/web/assets/dist/tests.js');

        $outputScript = <<<EOT
            <script>
                const tests = {$testString}
            </script>
            EOT;

        return TemplateHelper::raw($outputScript);

    }
}
