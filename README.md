# Notice manager for WordPress Dashboard
A small library to easily manage notice messages in the WordPress dashboard

## Installation
Use [Composer](https://getcomposer.org/) to require the library.

```bash
composer require antonioeatgoat/wp-dashboard-notice
```

Then include the [Composer](https://getcomposer.org/) autoload file in your project::

```php
require_once 'vendor/autoload.php'
```

If that's not an option then clone or download the package and require the `wp-dashboard-notice/autoload.php` file in your code:

```php
require_once 'path/to/wp-dashboard-notice/autoload.php';
```

Where `path/to/wp-dashboard-notice/autoload.php` is the absolute path to the `autoload.php` file.

## Example

```php
$notice = ( new NoticeFactory() )->create(
	'hello-world-notice',
	'<strong>Hello World</strong> - This is a sample notice',
	array(
		'title'          => 'This is a sample title',  
		'show_close_btn' => true,
		'cta_text'       => 'This is a custom CTA!',
		'cta_href'       => '#',
		'dismiss_text'   => "Don't show it again",
		'dismiss_mode'   => 'global',
		'status'         => 'success'
	));
NoticesManager::init()->register_notice($notice);
```
## Parameters
The notice creations needs three parameters:
- **id:** *(string)* An unique id to identificate it (it is also used as an id attribute in the HTML of the notice message printed).
- **message:** *(string)* The actual notice message.
- **args:** *(array)* Optional. An array of parameters containing more configurations.

Here's how the configurations can be used.

### Custom CTA
You can print the custom CTA button using these arguments, both of them are required to display the CTA.
- **cta_text:** The text of the button.
- **cta_href:** The link where the CTA points to.

### Permanent dismissing
The notice message can be dismissed permanently, clicking on a specific link. It supports three dismissing modes:
- **none:** *(default)* Notice message cannot be dismissed and dismissing link isn't displayed.
- **global:** Once dismissed, the notice message isn't shown again for nobody.
- **user:** Once dismissed, the notice message is dismissed only for the current user. Other users will continue to see it.

You can print the dismissing link using these arguments, both of them are required to display the link.
- **dismiss_text:** The text of the link.
- **dismiss_mode:** The dismissing mode explained above.

### Others
Other arguments of the parameters array are:
- **Title:** *(string)* An eventual title, displayed above the message.
- **status:** The status the notice message. Available values are *"info" (default)*, *"success"*, *"warning"*, *"error"*.
- **show_close_btn:** *(bool)* Default *false*. If a button to close the notice message is displayed. **Notce:** This will only close the notice, not dismiss it. On the page refresh it will be displayed again if the code requires it. This is useful when you have a "single time" notice message. That haven't stands permanently on the page, such as the notice message "Plugin activated" when you active a plugin.
