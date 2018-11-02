<?php
// This client for local_seb_authkey is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_seb_authkey
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @author Amr Hourani
 */

/// MOODLE ADMINISTRATION SETUP STEPS
// 1- Install the plugin
// 2- Enable web service advance feature (Admin > Advanced features)
// 3- Enable preferred web service protocols (Admin > Plugins > Web services > Manage protocols)
// 4- Create a token for a specific user (or at site level if wanted; i.e admin user) and for the service 'SEB Authentication Key Web Serice' (Admin > Plugins > Web services > Manage tokens)
// 5- Endpoint will be dependant on the used protocol: MOODLE_DOMAIN/webservice/PROTOCOL/server.php
//    ie: Endpoint for xmlrpc:  MOODLE_DOMAIN/webservice/xmlrpc/server.php
//    ie: Endpoint for soap:  MOODLE_DOMAIN/webservice/soap/server.php
//    ie: Endpoint for rest:  MOODLE_DOMAIN/webservice/rest/server.php

// !!WARNING!!: you should never allow the token to be non-expired. Please expire the tokens in moodle to as soon as the exam finishes.

// This example is based on xmlrpc

/// SETUP - NEED TO BE CHANGED

$token = '9746e30a18e2c2e315ffcd2922ef0b45';
$domainname = 'http://localhost/moodle';

/// FUNCTION NAME
$functionname = 'local_seb_authkey_add_seb_key';

/// PARAMETERS
$key = '70004716ADDBA0DE8EF6FAA5FA1F10EF694AEE082A2E8CC0E358F196CBBCD0F4';
$quizcmid = 3;

///// XML-RPC CALL
header('Content-Type: text/plain');
$serverurl = $domainname . '/webservice/xmlrpc/server.php'. '?wstoken=' . $token;

require_once('./curl.php');

$curl = new curl;
$post = xmlrpc_encode_request($functionname, array($key, $quizcmid), array ('encoding' => 'utf-8'));

$resp = xmlrpc_decode($curl->post($serverurl, $post));
print_r($resp);
