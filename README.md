# MuckiSearchPlugin
Shopware 6 plugin for Elasticsearch server integration.

## Features
- Full easy integration of the Elasticsearch server
- Basic Authentication or API Key Authentication for the search server 
- Automatic indexing of all active products into search index
- Creates search indices in relation to sales channel and language
- Free configuration of product fields for the search index

## System Requirements
- Shopware Version 6.5.2
- Elasticsearch Version 7.x
- php 8.1 or higher
## Installation
```shell
composer require muckiware/search-plugin
bin/console plugin:install -a MuckiSearchPlugin
```
## Configuration
- Go to Extensions -> My extensions
- Select the _Configure_-menu item of the Mucki Search Plugin
- Activate the plugin
- Enter the Elasticsearch Server connection host and port. Usually localhost:9200


## Uninstallation
Removes plugin and removes all plugin data
```shell
bin/console plugin:uninstall MuckiSearchPlugin
```
Removes plugin but keeps the plugin data
```shell
bin/console plugin:uninstall MuckiSearchPlugin --keep-user-data
```

## Testing
Start unit test
```shell
./vendor/bin/phpunit --configuration="custom/plugins/MuckiSearchPlugin" --testsuite "migration"
```
