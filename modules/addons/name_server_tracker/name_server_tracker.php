<?php
/**
 * WHMCS Name Server Tracker
 *
 * @see https://developers.whmcs.com/addon-modules/
 */

/**
 * Require any libraries needed for the module to function.
 * require_once __DIR__ . '/path/to/library/loader.php';
 *
 * Also, perform any initialization required by the service's library.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\NameServerTracker\Admin\AdminDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define addon module configuration parameters.
 *
 * Includes a number of required system fields including name, description,
 * author, language and version.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * Examples of each and their possible configuration parameters are provided in
 * the fields parameter below.
 *
 * @return array
 */
function name_server_tracker_config(){
    return [
        // Display name for your module
        'name' => 'Name Server Tracker',
        // Description displayed within the admin interface
        'description' => 'This module provides a single spot to easily reference name servers throughout your network',
        // Module author name
        'author' => 'Websavers Inc.',
        // Default language
        'language' => 'english',
        // Version number
        'version' => '1.0',
        'fields' => [
            // a text field type allows for single line text input
            'nsdomain' => [
                'FriendlyName' => 'Name Server Domain',
                'Type' => 'text',
                'Size' => '40',
                'Default' => '',
                'Description' => 'Enter the domain you use for all of your name servers without the ns* or dns* prefix.',
            ],
          /*
            // a password field type allows for masked text input
            'Password Field Name' => [
                'FriendlyName' => 'Password Field Name',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter secret value here',
            ],
            // the yesno field type displays a single checkbox option
            'Checkbox Field Name' => [
                'FriendlyName' => 'Checkbox Field Name',
                'Type' => 'yesno',
                'Description' => 'Tick to enable',
            ],
            // the dropdown field type renders a select menu of options
            'Dropdown Field Name' => [
                'FriendlyName' => 'Dropdown Field Name',
                'Type' => 'dropdown',
                'Options' => [
                    'option1' => 'Display Value 1',
                    'option2' => 'Second Option',
                    'option3' => 'Another Option',
                ],
                'Default' => 'option2',
                'Description' => 'Choose one',
            ],
            // the radio field type displays a series of radio button options
            'Radio Field Name' => [
                'FriendlyName' => 'Radio Field Name',
                'Type' => 'radio',
                'Options' => 'First Option,Second Option,Third Option',
                'Default' => 'Third Option',
                'Description' => 'Choose your option!',
            ],
            // the textarea field type allows for multi-line text input
            'Textarea Field Name' => [
                'FriendlyName' => 'Textarea Field Name',
                'Type' => 'textarea',
                'Rows' => '3',
                'Cols' => '60',
                'Default' => 'A default value goes here...',
                'Description' => 'Freeform multi-line text input field',
            ],
          */
        ]
    ];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function name_server_tracker_activate(){
    // Create custom tables and schema required by your module
    try {
        Capsule::schema()
            ->create(
                'mod_name_server_tracker',
                function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->text('nameserver');
                    $table->text('ip');
                    $table->text('server_hostname');
                    $table->timestamps(); //created_at, updated_at
                }
            );

        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'Module activated and database table created.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create mod_name_server_tracker: ' . $e->getMessage(),
        ];
    }
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 * Use this function to undo any database and schema modifications
 * performed by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function name_server_tracker_deactivate(){
    // Undo any database and schema modifications made by your module here
    /*
    try {
        Capsule::schema()
            ->dropIfExists('mod_name_server_tracker');

        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'Successfully deactivated module.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to drop mod_name_server_tracker: {$e->getMessage()}",
        ];
    }
    */
    return [
        // Supported values here include: success, error or info
        'status' => 'success',
        'description' => 'Successfully deactivated module. Please note: for data integrity, the table mod_name_server_tracker was not removed.',
    ];
}

/**
 * Upgrade.
 *
 * Called the first time the module is accessed following an update.
 * Use this function to perform any required database and schema modifications.
 *
 * This function is optional.
 *
 * @see https://laravel.com/docs/5.2/migrations
 *
 * @return void
 */
function name_server_tracker_upgrade($vars){
    $currentlyInstalledVersion = $vars['version'];

    /// Perform SQL schema changes required by the upgrade to version 1.1 of your module
    if ($currentlyInstalledVersion < 1.1) {
      /*
        $schema = Capsule::schema();
        // Alter the table and add a new text column called "demo2
        $schema->table('mod_name_server_tracker', function($table) {
            $table->text('demo2');
        });
      */
    }
    
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * This function is optional.
 *
 * @see name_server_tracker\Admin\Controller::index()
 *
 * @return string
 */
function name_server_tracker_output($vars){
    
    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    echo $response;
}
