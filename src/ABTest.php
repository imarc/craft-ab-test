<?php

namespace imarc\abtest;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\Db;

use craft\web\twig\variables\Cp;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;
use imarc\abtest\models\Settings;
use imarc\abtest\elements\ABTest as TestElement;
use imarc\abtest\variables\ABTestVariable;

/**
 * AB Test plugin
 *
 * @method static ABTest getInstance()
 * @author Linnea Hartsuyker <info@imarc.com>
 * @copyright Linnea Hartsuyker
 * @license MIT
 */
class ABTest extends Plugin
{

    public string $schemaVersion = '1.0.0';
    //public bool $hasCpSection = true;
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });

        if (Craft::$app->request->isCpRequest) {
            $this->controllerNamespace = 'imarc\\abtest\\controllers';
        } elseif (Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'imarc\\abtest\\controllers\\console';
        } else {
            $view = Craft::$app->getView();
            
            $distUrl = Craft::$app->assetManager->getPublishedUrl('@imarc/abtest/web/assets/dist/tests.js');
            $view->registerJsFile($distUrl, array_merge(['defer' => true]));
        }
    }

    protected function createSettingsModel(): ?craft\base\Model
    {
        return new Settings;
    }

    // protected function settingsHtml(): ?string
    // {
    //     return Craft::$app->getView()->renderTemplate(
    //         'ab-test/settings',
    //         ['settings' => $this->getSettings()]
    //     );
    // }

    private function attachEventHandlers(): void
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'ab-test',
                    'label' => 'A/B Testing'
                ];
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function (RegisterUrlRulesEvent $event) {
                $event->rules['ab-test/tests/new'] = 'ab-test/a-b-tests/edit';
                $event->rules['ab-test/tests/<testId:\d+>'] = 'ab-test/a-b-tests/edit';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('abtest', ABTestVariable::class);
            }
        );

        Event::on(
            TestElement::class,
            Element::EVENT_AFTER_DELETE,
            static function ($event) {
                $element = $event->sender;

                Db::delete('{{%abtest_tests}}', [
                    'id' => $element->id,
                ]);

                Db::delete('{{%abtest_options}}', [
                    'testId' => $element->id,
                ]);
            }
        );
    }
}

