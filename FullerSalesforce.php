<?php
namespace FullerSalesforce;

/**
 * Push Alumni Referal Information into Fuller Salesforce Instance
 *
 * The FullerSalesforce class sets up a connection to Salesforce and 
 * pushes alumni referals into Salesforce using the SForce API. This 
 * class requires that the SforcePartnerClient.php library from 
 * the Force.com-Toolkit-for-PHP toolkit has been "required" before
 * this class is loaded.
 */
class FullerSalesforce {
    // Holds the connection to Salesforce
    private $connection;
    // Only these column names will be accept as valid
    private $valid_columns = array("LeadSource","Alumni_Email__c","Alumni_First_Name__c",
                                   "Alumni_Last_Name__c",
                                   "FirstName","LastName","MailingStreet",
                                   "MailingCity","MailingState","MailingPostalCode",
                                   "MailingCountry","Preferred_Phone__c","Email",
                                   "TargetX_SRMb__Gender__c","Adjectives__c",
                                   "Alumni_Timestamp__c","Alumni_IP__c");
    // All records MUST have values defined in these column
    private $required_columns = array("LastName");


    /**
     * Constructor
     *
     * Creates an object from the class
     *
     * @param string $username The Salesforce Username
     * @param string $password The Salesforce Password
     * @param string $security_token The Salesforce Security Token, this is 
     * required to use the API. 
     * https://help.salesforce.com/HTViewHelpDoc?id=user_security_token.htm&language=en_US
     * @param string $wsdl_file This is the path to the Partner WSDL XML file, it is provided
     * in the Force.com-Toolkit-for-PHP toolkit.
     *
     * @return FullerSalesforce A FullerSalesforce object
     */
    function __construct($username, $password, $security_token, $wsdl_file) {
        $this->connection = new \SforcePartnerClient();
        $this->connection->createConnection($wsdl_file);
        $this->connection->login($username, $password.$security_token);
    }


    /**
     * Adds a lead to Salesforce
     *
     * @param array $record An associative array with column names and values
     * that will be pushed into Salesforce. Currently 'Alumni_Last_Name__c' is
     * the only required column.
     *
     * @throws Exception Throws a general exception if the record does not have
     * the required columns or if the Salesforce record creation fails. The 
     * in the exception will try 
     * @throws SoapFault If the API itself runs into trouble it will throw
     * a SoapFault exception. This class makes no effort to catch these 
     * exceptions.
     *
     * @returns array The response array returned by Salesforce. This contains 
     * the ID of the created record.
     */
    function addLead($record) {

        // Check the record for required columns
        foreach ($this->required_columns as $r) {
            if (!in_array($r, array_keys($record))) {
                throw new \Exception("New Record is missing required field: $r");
            }
            // A required field cannot be an empty string
            if (trim($record[$r]) == "") {
                throw new \Exception("New Record has empty string in required field: $r");
            }
        }

        // Check the record for invalid columns
        foreach (array_keys($record) as $record_key) {
            if (!in_array($record_key, $this->valid_columns)) {
                throw new \Exception("$record_key is not a valid field for a new record");
            }
        }

        // Add the accountID
        $records[0] = new \SObject();
        $records[0]->fields = $record;
        $records[0]->type = 'Contact';
        $response = $this->connection->create($records);

        // If the add didn't succeed throw an exception with a, hopefully, 
        // useful message
        if (!$response[0]->success) {
            $m = $response[0]->errors[0]->message;
            $s = $response[0]->errors[0]->statusCode;
            throw new \Exception("Insert Failed: Status - $s, Message -$m");
        }

        // Returns the array that was return by the API call
        return $response;
    }
}

?>
