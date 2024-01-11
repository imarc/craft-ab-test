# AB Test

Allows a user to set up an A/B test in Craft, track impressions and clicks with GA-4, and serve different versions. The serving is done in the front end, so front end caching will not interfere with the test.

## Requirements

This plugin requires:

1. Craft CMS 4.5.0 or later
2. PHP 8.0.2 or later.
3. The Google Tag Manager script installed on the page

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

## Usage

#### 1. Add Code to Templates

Add the following code in the footer script block of any page where you'd like to conduct your tests
```
 {{ craft.abtest.abTestScript }}
```

#### 2. Set up tests in the Control Panel

#### 3. Set up Google Tag Manager Dimensions

## Testing and Verification

