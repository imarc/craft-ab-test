<?php

namespace imarc\abtest\elements;

use DateTime;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;

use imarc\abtest\actions\DeleteAction;
use imarc\abtest\records\ABTest as TestRecord;

class ABTest extends Element
{

    const TEST_TABLE = '{{%abtest_tests}}';

    public bool $hardDelete = true;
    public ?string $name = null;
    public ?string $handle = null;
    public ?string $startAt = null;
    public ?string $endAt = null;
    public bool $enabled = true;
    public $targetedUrls = null;
    public ?string $targetedSelector = null;

    public function rules(): array
    {
        return [
            [['name', 'handle', 'targetedUrls', 'targetedSelector'], 'required'],
            [['handle'],
                'unique',
                'targetClass' => TestRecord::class,
                'filter' => function($query) {
                    if ($this->id !== null) {
                        $query->andWhere('`id` != :id', ['id' => $this->id]);
                    }
                }
            ],
            [['name'],
                'unique',
                'targetClass' => TestRecord::class,
                'filter' => function ($query) {
                    if ($this->id !== null) {
                        $query->andWhere('`id` != :id', ['id' => $this->id]);
                    }
                }
            ],
            [['handle'], 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/']
        ];
    }

    public function isNew(): bool
    {
        return !$this->id;
    }

    public static function displayName(): string
    {
        return Craft::t('ab-test', 'A/B Test');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('ab-test', 'A/B Test');
    }

    public static function refHandle(): ?string
    {
        return 'test';
    }

    public static function find(): ElementQueryInterface
    {
        return new ABTestQuery(static::class);
    }

    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert(self::TEST_TABLE, $this->_getInsertData())
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update(self::TEST_TABLE, $this->_getUpdateData(), ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    public function cpEditUrl(): ?string
    {
        $path = sprintf('ab-test/tests/%s', $this->id);
        return UrlHelper::cpUrl($path);
    }

    public function getUiLabel(): string
    {
        return $this->name;
    }

    public function getCDpEditUrl(): ?string
    {
        $cpEditUrl = $this->cpEditUrl();

        if (!$cpEditUrl) {
            return null;
        }

        $params = [];

        return UrlHelper::urlWithParams($cpEditUrl, $params);
    }

    public function canView(User $user): bool
    {
        return true;
    }

    public static function defineDefaultTableAttributes(string $source): array
    {
        return ['enabled', 'startAt', 'endAt'];
    }

    public function canDelete(User $user): bool
    {
        return true;
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'enabled' => Craft::t('app', 'Enabled?'),
            'startAt' => Craft::t('app', 'Start date'),
            'endAt' => Craft::t('app', 'End date'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['name'];
    }

    protected static function defineActions(string $source = null): array
    {
        return [
            DeleteAction::class,
        ];
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => 'All Tests',
                'criteria' => []
            ],
        ];
    }

    private function _getInsertData(): array
    {
        if (gettype($this->targetedUrls) == 'string') {
            $this->targetedUrls = json_encode($this->targetedUrls);
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'handle' => $this->handle,
            'enabled' => $this->enabled,
            'startAt' => $this->startAt,
            'endAt' => $this->endAt,
            'targetedUrls' => $this->targetedUrls,
            'targetedSelector' => $this->targetedSelector
        ];

        //'targetedUrls' => json_encode($this->targetedUrls),
    }

    private function _getUpdateData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'handle' => $this->handle,
            'enabled' => $this->enabled,
            'startAt' => $this->startAt,
            'endAt' => $this->endAt,
            'targetedUrls' => $this->targetedUrls,
            'targetedSelector' => $this->targetedSelector
        ];

        //'targetedUrls' => json_encode($this->targetedUrls),
    }

}
