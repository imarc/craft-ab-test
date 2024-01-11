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

Click on the **A/B Testing** item in the left nav. Then click on "New Test".

You will need to set up the following:
- **Name:** The name the test will be refered to in the list of tests. Should be something descriptive like "Buy Now Link"
- **Handle:** A string with no spaces, e.g. "buyNowLink" that is used to:
  1. Refer to the test when setting up Google Analytics custom dimension (see below)
  2. Store what test and option the user has seen in cookies
  3. Refer to the test in a query param when verifying the tests (see below)
- **Enabled**: If this is set to off, the test will not display. If it is set to on, the test will display if the current date and time is between the start and the end dates.
- **Targeted Element Selector:** The element on the page that the test should target. The test will replace the inner HTML for whatever is within this element. Element Selectors are a powerful way of targeting a specific element on a page. An example might be `h2.shoppingcart__title` and would refer to an element that looked like this: `<h2 class="shoppingcart_title"></h2>`. The test will replace what is inside the `<h2>` tags. [Here is a reference for how to construct selectors](https://www.w3schools.com/cssref/css_selectors.php). It's important to construct a selector that only retrieves one element on the page. The test will only target the first instance of that element.
- **Targeted URLs:** The url or urls where the test should show up. The test can target a single page, a list of pages, or pages with wildcards, e.g. `https://yourdomainhere.net/product/*/detail` or `https://yourdomainhere.net/*`. You can use more than one wild card. Query strings will be ignored. *Note: two tests can be targeted to the same page, but if more than one test targets the same page and same selector, the behavior will be unpredictable.*
- **Options:** The different versions that you want to test. These have the following fields:
  - Name: A descriptive name of the option
  - Handle: used similarly to handle above. In Google Analytics, each custom dimension will have the test handle and option handle (see below)
  - Inner HTML: What will be substituted into the targeted element. This should be well-formed HTML or you risk breaking the page.
  - Weight: How often this option should appear compared to other options. The total weight should add up to 100.
- **Starts At:** The date and time that the test should start. If left blank, the test will start immediately.
- **Ends At:** The date and time that the test will end. If left blank, the test will run indefinitely.

#### 3. Set up Google Tag Manager Dimensions
In order to track which test options are shown and link it to other user behaviors, you will need to set up a custom demension in GA4. To do this:
1. Login to Google Analytics, and edit the target domain
2. Go into Admin (in the bottom left as of this writing)
3. Under Data Display click "Custom Definitions"
4. Click the button in the top right to "Create custom dimension"
5. Set the following values:
   - **Dimension Name:** Can be anything, might want to match the test name
   - **Scope**: Event
   - **Event Parameter**: The test handle
6. Click "Create"

## Testing and Verification
This is a powerful tool that can easily break your website, so it's recommended to test your tests. If you add `?testname=[testHandle]&option=[optionHandle]` to a URL where the test should appear, you will see the test and option that matches those handles. This only works if the test is enabled and within the run dates. If you add `?useCookies=false` to a URL where a test should appear, you will see the test randomize per the weighting, but the test you see will not be stored or taken from a cookie, so you should see the tests change as you refresh, depending on the weighting.

#### Recommended Process for Testing
1. Set up the test
2. Target a single page
3. Set a default options where the innerHTML is the current innerHTML of the element. Give this a 100 weighting.
4. Set up other options, and give them a 0 weighting.
5. Use query parameters to preview the options.
6. Once they look right, adjust the weighting, remove the Default option if desired, and set the start and end date.


