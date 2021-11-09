# Magento 2 Alternate URLs

Makes it possible to configure and add alternate URLs to the HEAD of each page.
The link to other websites/stores can be created manually in the admin

## Installation

This package can be installed using [Composer](https://getcomposer.com).

```bash
composer require elgentos/magento2-alternate-urls
bin/magento module:enable Elgentos_AlternateUrls
bin/magento setup:upgrade
```

## Usage

To use the module, you'll have to enable the module and add some mapping to 
"connect" websites/stores to each other. The script will automatically add 
references to the HEAD of the page for each link that is found for the current
page.

## Extending for other page types

To extend this functionality to other page types (like blog posts from a module),
you will have to create a custom module and add a custom type that implements 
`Elgentos\AlternateUrls\Type\TypeInterface`. After that you'll need to add your
custom type to the `typeInstances` in the `alternate_urls` block in `default.xml`.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
proprietary