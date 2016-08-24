# Pied Piper Sharepoint

This module allows for connecting to the Pied Piper Sharepoint API. It comes with several dependencies that must be satisfied.

## Installation

Install the composer dependencies.



The Rest client needs to have access to the user credentials in order to access the sharepoint API. These can be added to the settings.php

````
$conf['sharepoint_user'] = 'username';
$conf['sharepoint_password'] = 'password';
````

## Utilization

To create a new connection to the sharepoint API instantiate a new API object, passing in the sharepoint username and password. *Note: Do not store any credentials in plan text in the codebase. Use Drupal's variable system (see Installaion)/
 
    $api = new \Piper\Sharepoint\API('user', 'password');

To post a new item to the "Piper Compression Drupal List" do the following:

1) create an associative array of data to be serialized into a json object and sent in the request. 

````
$json = [
  'sharepoint_field_name' => 'field_value',
  'sharepoint_field_name' => 'field_value',
  'sharepoint_field_name' => 'field_value',
];
````

2) Post the data to the sharepoint API using the API::createItem($json_data) method.

````
$api->createItem($json_data);
````

## Development

The Sharepoint API credentials which can access sharepoint can also authenticate through NTLM will also be needed.
