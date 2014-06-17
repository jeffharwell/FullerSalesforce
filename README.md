This code can be used to push records into the Fuller Salesforce Instance.

It is dependent on the salesforce toolkit Force.com-Toolkit-for-PHP which
can be obtained from:
    https://github.com/developerforce/Force.com-Toolkit-for-PHP

It contains the following files.

FullerSalesforce.php
  - This is the class which contains the code and logic for 
    connecting to the Salesforce API and uploading an Alumni
    referral record.

fsf_test.php
  - This is an program that uses the FullerSalesforce
    class to upload an Alumni referral record. It is intended
    as an example of how to use the FullerSalesforce class.

config.php.example
  - This is an example file that contains the credentials
    needed to log into the Salesforce API. It is used
    by the fsf_test.php program. Copy it to config.php 
    and put in the account credentials that the
    program should use to log into the Salesforce API.
