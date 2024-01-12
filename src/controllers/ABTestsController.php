<?php

namespace imarc\abtest\controllers;

use DateTime;
use Craft;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use craft\web\View;
use imarc\abtest\elements\ABTest as TestElement;
use imarc\abtest\elements\Option;
use imarc\abtest\records\ABTest as TestRecord;
use imarc\abtest\records\Option as OptionRecord;
use yii\web\Response;

class ABTestsController extends Controller
{
    protected ?TestElement $test;

    protected array $params = [
        'name' => 'string',
        'handle' => 'string',
        'targetedSelector' => 'string',
        'targetedUrls' => 'array',
        'startAt' => 'datetime',
        'endAt' => 'datetime',
        'enabled' => 'boolean'
    ];

    public function actionEdit(): Response|string
    {
        $test = $this->getTestForEdit();

        //return json_encode($test);

        if (!$test) {
            return $this->redirect('ab-test');
        }

        if ($test->isNew()) {
            $options = [];
        } else {
            $testRecord = TestRecord::findOne($test->id);
            if ($testRecord) {
                $options = $testRecord->getOptions()->all();
            }
        }

        $data = [
            'test' => $test,
            'options' => $options,
        ];

        return $this->renderTemplate('ab-test/_edit', $data, View::TEMPLATE_MODE_CP);
    }

    public function actionSave()
    {

        //return json_encode($this->request->getBodyParams());
        $testId = $this->request->getBodyParam('testId');

        $this->test = $testId ? TestElement::findOne($testId) : new TestElement();

        if ($testId && !$this->test) {
            $this->setFailFlash(Craft::t('ab-test', 'Test ID not found'));
            return null;
        }

        $dateErrors = $this->validateDates();

        if (count($dateErrors) > 0) {
            $this->setFailFlash(Craft::t('ab-test', 'Invalid dates'));

            $this->test->addErrors($dateErrors);

            Craft::$app->urlManager->setRouteParams([
                'test' => $this->test
            ]);

            return null;
        }

        $this->setParams();

        if (!Craft::$app->elements->saveElement($this->test)) {
            
            $this->setFailFlash(Craft::t('ab-test', 'Could not save test'));

            Craft::$app->urlManager->setRouteParams([
                'test' => $this->test
            ]);

            return null;
        }

        $optionErrors = $this->saveOptions($this->request->getBodyParam('options'));

        if (count($optionErrors)) {
            //$this->setFailFlash(Craft::t('ab-test', 'Failed to save test options'));
            $this->setFailFlash(Craft::t('ab-test', implode(',', $optionErrors)));

            $this->test->addErrors($optionErrors);

            if (!$testId) {
                TestRecord::findOne($this->test->id)->delete();
                $this->test->id = null;
            }

            Craft::$app->urlManager->setRouteParams([
                'test' => $this->test,
                'options' => $this->request->getBodyParam('options')
            ]);


        }

        //return json_encode($this->test->getErrors());
        $this->setSuccessFlash(Craft::t('ab-test', 'Experiment saved.'));

        $this->redirect('ab-test');
    }

    private function getTestForEdit(): TestElement
    {
        $routeParams = Craft::$app->urlManager->getRouteParams();

        // failed request, return old model to repopulate form
        if (isset($routeParams['test']) && $routeParams['test']) {
            return $routeParams['test'];
        }

        // edit
        if (isset($routeParams['testId']) && $routeParams['testId']) {
            return TestElement::findOne($routeParams['testId']);
        }

        // create
        return (new TestElement());

    }

    private function saveOptions(array $options): array
    {
        $newIds = [];
        $errors = [];
        $weightTotal = 0;

        foreach($options as $key => $optValues) {
            if ($optValues['name'] && $optValues['handle']) {
                $option = new Option();
                $option->testId = $this->test->id;
                $option->name = $optValues['name'];
                $option->handle = $optValues['handle'];
                $option->weight = (int)$optValues['weight'];
                $option->innerHTML = $optValues['innerHTML'];

                $weightTotal += $option->weight;

                if ($option->name === null || empty($option->name)) {
                    $errors[] = "Option {$key} must have a name";
                }

                if ($option->handle === null || empty($option->name)) {
                    $errors[] = "Option {$key} must have a handle";
                }

                if ($option->weight === null || $option->weight <= 0) {
                    $errors[] = "Option {$key} must have weight set";
                }

                if ($option->weight < 0 || $option->weight > 100) {
                    $errors[] = "Weight must be between 0 and 100 on options {$key}";
                }

                if (!Craft::$app->elements->saveElement($option)) {
                    $errors[] = "Option {$key} failed to save";
                }

                $newIds[] = $option->id;

            }

        }

        if ($weightTotal !== 100) {
            $errors[] = "Total weight of all options must add up to 100";
        }

        if ($newIds && count($newIds) > 0) {
            $newIds = implode(",", $newIds);

            OptionRecord::deleteAll("testId={$this->test->id} AND id NOT IN ({$newIds})");
        }
        
        return $errors;

    }

    private function setParams()
    {
        $values = Craft::$app->request->getBodyParams();

        foreach ($this->params as $key => $type) {
            if (!isset($values[$key])) {
                continue;
            }
            $value = $values[$key];

            if ($type === 'boolean') {
                $value = (bool)$value;
            } elseif ($type === 'integer') {
                $value = (int)$value;
            } elseif ($type === 'datetime') {
                if ($value['date']) {
                    $value = (new DateTime($value['date']))->format('Y-m-d H:i:s');
                } else {
                    $value = null;
                }
            }

            $this->test->{$key} = $value;
            
        }

        
    }

    private function validateDates(): array
    {
        $params = Craft::$app->request->getBodyParams();
        $errors = [];

        $startAt = $params['startAt'];
        $endAt = $params['endAt'];

        $startDate = DateTimeHelper::toDateTime($startAt);
        // if (!$startDate) {
        //     $errors['startAt'] = "Invalid datetime format";
        // }

        $endDate = DateTimeHelper::toDateTime($endAt);
        // if (!$endDate) {
        //     $errors['endAt'] = "Invalid datetime format";
        // }

        if ($startDate && $endDate && $startDate > $endDate) {
            $errors['startAt'] = 'End date much be after start date';
        }

        return $errors;


    }


}
