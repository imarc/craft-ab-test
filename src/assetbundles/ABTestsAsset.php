<?php

namespace imarc\abtest\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

class ABTestsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@imarc/abtest/web/assets/dist';
        $this->depends = [
            CpAsset::class,
            VueAsset::class,
        ];

        $this->js = [
            'checkTest.js',
        ];

        parent::init();
    }
}