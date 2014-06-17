<?php
//Modify to where you installed the Force.com-Toolkit-for-PHP
require_once('salesforce/Force.com-Toolkit-for-PHP/soapclient/SforcePartnerClient.php');
require('config.php');

// Here is the FullerSalesforce PHP class
require("FullerSalesforce.php");

// The WSDL file, you will probably need to modify the path
$wsdl_file = 'salesforce/Force.com-Toolkit-for-PHP/soapclient/partner.wsdl.xml';

// Initialize our object
// username, password, security token, and path to the WSDL Partner XML file
$sfs = new FullerSalesForce\FullerSalesForce("jgroff@160over90.com", "fuller2014", "KGJBIxuLviYtpjCqedGTifc82", $wsdl_file);

// Here is the record that we want to add.
$record = array(
    'Alumni_Email__c' => 'jharwell@fuller.edu',
    'Alumni_Last_Name__c' => 'Harwell',
    'Alumni_First_Name__c' => 'Jeff',
    'FirstName' => 'Jane',
    'LastName' => 'DoeTestThree',
    'MailingStreet' => '555 Test Lane',
    'MailingCity' => 'Pasadena',
    'MailingState' => 'CA',
    'MailingPostalCode' => '91182',
    'MailingCountry' => 'US',
    'Preferred_Phone__c' => '555-555-5555',
    'Email' => 'janedoetestthree@blackhole.org',
    'TargetX_SRMb__Gender__c' => 'Female',
    'Adjectives__c' => 'Tall, Loves Coffee',
    'Alumni_Timestamp__c' => '2014060132149',
    'Alumni_IP__c' => '127.0.0.1'
);

/* These were invalid before
MailingStreet
MailingCity
MailingState
MailingPostalCode
MailingCountry
Preferred_Phone__c
Email
TargetX_SRMb__Gender__c
 */

// Add it
$resp = $sfs->addLead($record);

// $resp is the value returned by the API
var_dump($resp);

// Grab the Record ID
$id = $resp[0]->id;
echo "Id is $id";

// Writing the newly added record IDs to a file, so that I can 
// pass them to the Salesforce administrator so that they can
// delete the test data
$id_file = 'created_ids.txt';
$handle = fopen($id_file, 'a') or die("Cannot open $id_file\n");
fwrite($handle, $id."\n");
fclose($handle);

?>
