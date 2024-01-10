# AB Test

Allows a user to set up an A/B test in Craft, track impressions and clicks with GA-4, and serve different versions. The serving is done in the front end, so front end caching will not interfere with the test.

## Requirements

This plugin requires Craft CMS 4.5.0 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “AB Test”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require imarc/craft-ab-test

# tell Craft to install the plugin
./craft plugin/install ab-test
```
