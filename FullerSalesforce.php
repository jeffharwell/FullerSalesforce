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
    // Holds the potential strings that could be entered to
    // indicate "the United States of Ameria" as the mailing country
    private $usa_strings = array("United States of America",
                                 "The United States of America",
                                 "USA",
                                 "US",
                                 "United States",
                                 "America");
    // Regex pattern of characters to replace in phone numbers
    // before pushing to salesforce
    private $phone_replacement_regex = "/[-\)\(\+\.]+/";

    // Debug flag, if debug is set the class will not push to
    // salesforce.
    private $debug = False;


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
     * Set the debug flag
     *
     * The debug flag instructs the class to not push to Salesforce, but
     * instead to var_dump the array that it would push to Salesforce. Sort
     * of like a dry run.
     */
    function setDebug() {
        $this->debug = True;
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

        // Per Chris Lux Fuller's Salesforce data standards require that the interface 
        // put in a NULL for the country if USA is specified, meaning that for USA
        // we unset the 'MailingCountry' value.
        //
        // Notice that the algorithm is doing a case-insensitive match that ignores 
        // beginning and ending whitespace
        
        // Convert our string of ways to specify USA to lowercase
        $lc_usa_strings = array_map("strtolower", $this->usa_strings);

        // Do the compare, case insensitive and ignoring ending and beginning whitespace
        if (array_key_exists('MailingCountry', $record)) {
            if (in_array(strtolower(trim($record['MailingCountry'])), $lc_usa_strings)) {
                // Found a US country string, unset the record in accordance with the
                // Fuller SRM Data Standards
                unset($record['MailingCountry']);
            }
        }

        // Per Chris Lux Fuller's Salesforce data standards require that the delimeters
        // be removed from phone numbers

        if (array_key_exists('Preferred_Phone__c', $record)) {
            $record['Preferred_Phone__c'] = preg_replace($this->phone_replacement_regex,'',$record['Preferred_Phone__c']);
        }


        // Add the accountID
        $records[0] = new \SObject();
        $records[0]->fields = $record;
        $records[0]->type = 'Contact';


        if (!$this->debug) {
            // Not in debug mode, push the record into Salesforce and return the
            // result
            $response = $this->connection->create($records);

            // If the add didn't succeed throw an exception with a, hopefully, 
            // useful message
            if (!$response[0]->success) {
                $m = $response[0]->errors[0]->message;
                $s = $response[0]->errors[0]->statusCode;
                throw new \Exception("Insert Failed: Status - $s, Message -$m");
            }

            return $response;
        } else {
            // In debug mode
            // dump the record
            var_dump($records);

            // uugh -- put together a error object that looks like
            // something Salesforce would generate so that debug 
            // mode doesn't nuke downstream code
            $respobj = new \stdClass;
            $respobj->id="xxxDEBUGMODExxx";
            $respobj->success = False;
            $errorobj = new \stdClass;
            $errorobj->message = "In Debug Mode, no Record Pushed to Salesforce";
            $errorobj->statusCode = 50000;
            $respobj->errors = array($errorobj);

            // Salesforce always returns an array of response objects
            return array($respobj);
        }
    }
}

?>
