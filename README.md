# WooCommerce API Call with Custom Fields

This is a basic add_action script for Woocommerce that lets you pass along custom field data.

## Installation

Add this to your functions.php file or a snippets plugin.

## Notes

This script will scan new Woocommerce orders for any products with a category tag 'API' then build a payload which includes custom data that a customer enters on the product page, such as a text field or text area.

The payload is passed to the API via POST and success/fail metadata is created on the order id page.

Logs and error data are stored in woocommerce > status > logs > api-logs

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.
