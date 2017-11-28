![Accredible Logo](https://s3.amazonaws.com/accredible-cdn/accredible_logo_sm.png)

# Accredible API PHP SDK
![Build Status](https://travis-ci.org/accredible/acms-php-api.svg?branch=master)

## Overview
The Accredible platform enables organizations to create, manage and distribute digital credentials as digital certificates or open badges.

An example digital certificate and badge can be viewed here: https://www.credential.net/10000005

This Composer package wraps the Accredible API in PHP for easy integration into projects. The full REST API documentation can be found here: http://docs.accrediblecredentialapi.apiary.io/ 

## Example Output
![Example Digital Certificate](https://s3.amazonaws.com/accredible-cdn/example-digital-certificate.png)

![Example Open Badge](https://s3.amazonaws.com/accredible-cdn/example-digital-badge.png)

## Install
```bash
composer require accredible/acms-php-api dev-master
```

## Usage

Add `use ACMS\Api;` to the class you'd like to use the API in.

```php
use ACMS\Api;

// Instantiate the API instance replacing APIKey with your API key
$api = new Api("APIKey");

// Get a Credential
$api->get_credential(10000005);

// Get an array of Credentials 
$api->get_credentials(null, "john@example.com");

// Create a Credential - Name, Email, Group ID
$api->create_credential("John Doe", "john@example.com", 54018);

// Update a Credential
$api->update_credential(10000005, "Jonathan Doe");

// Delete a Credential
$api->delete_credential(10000005);

// Get a Group
$api->get_group(100);

// Create a Group - Name, Course Name, Course Description, Course Link
$api->create_group("PHPTest", "Test course", "Test course description.", "http://www.example.com");

// Update a Group 
$api->update_group(100, 'PHPTest2');

// Delete a Group
$api->delete_group(100);
```

###Bug reports

If you discover any bugs, feel free to create an issue on GitHub. Please add as much information as possible to help us fixing the possible bug. We also encourage you to help even more by forking and sending us a pull request.

https://github.com/accredible/acms-php-api/issues

### License

This project is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Todo
* Make batch comsumption easier (example https://github.com/facebook/php-graph-sdk/blob/ad21129edb95196d04e4e69a464702215ad8c255/src/Facebook/FacebookBatchRequest.php) and document
* Add evidence item endpoints
* Add reference endpoints
* Add additional test suite using mocks
