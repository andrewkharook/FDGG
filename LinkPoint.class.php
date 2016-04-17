<?php

namespace Payment\LinkPoint;

/**
 * Class LinkPoint
 *
 * First Data Global Gateway (ex LinkPoint) integration
 *
 * @docs http://www.firstdata.com/downloads/marketing-merchant/fdgg-web-service-api.pdf
 * @author Andrew Kharook
 */
class LinkPoint
{
    const LIVE_API_URL = 'https://ws.firstdataglobalgateway.com/fdggwsapi/services/order.wsdl';
    const TEST_API_URL = 'https://ws.merchanttest.firstdataglobalgateway.com/fdggwsapi/services/order.wsdl';

    /**
     * @var string - the api username
     */
    protected $username = null;

    /**
     * @var string - the api password
     */
    protected $password = null;

    /**
     * @var string
     */
    protected $clientCertDir = './';

    /**
     * @var null
     */
    protected $clientCertPassword = null;

    /**
     * @var string - api transaction type
     */
    protected $transactionType = self::TRAN_SALE;

    /**
     * @var string
     */
    protected $transactionRecurring = self::TRANSACT_RECURRING_NO;

    /**
     * @var string
     */
    protected $transactionOrigin = self::TRANSACT_ORIGIN_ECI;

    /**
     * @var float
     */
    protected $chargeTotal = 0.00;

    /**
     *  the error code if one exists
     *
     * @var integer
     */
    protected $errorCode = 0;

    /**
     * the error message if one exists
     *
     * @var string
     */
    protected $errorMessage = '';

    /**
     *  the response message
     *
     * @var string
     */
    protected $response = '';

    /**
     *  the headers returned from the call made
     *
     * @var array
     */
    protected $headers = '';

    /**
     * The response represented as an array
     *
     * @var array
     */
    protected $arrayResponse = array();

    /**
     * All the post fields we will add to the call
     *
     * @var array
     */
    protected $postFields = array();

    /**
     * @var boolean - set whether we are in a test mode or not
     */
    public static $testMode = false;

    /**
     * Default options for curl.
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FRESH_CONNECT  => 1,
        CURLOPT_PORT           => 443,
        CURLOPT_USERAGENT      => 'curl-php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST           => 1,
        CURLOPT_HTTPHEADER     => array('Content-Type: text/xml'),
        CURLOPT_SSL_VERIFYPEER => 0,
    );

    /**
     * Transaction types
     */
    const TRAN_SALE = 'sale';
    const TRAN_FORCETICKET = 'ForceTicket';
    const TRAN_PREAUTH = 'preAuth';
    const TRAN_POSTAUTH = 'postAuth';
    const TRAN_RETURN = 'Return';
    const TRAN_CREDIT = 'Credit';
    const TRAN_VOID = 'Void';

    /**
     * Credit Card Code Indicators
     */
    const CC_CODE_NOT_PROVIDED = 'NOT_PROVIDED';
    const CC_CODE_PROVIDED = 'PROVIDED';
    const CC_CODE_ILLEGIBLE = 'ILLEGIBLE';
    const CC_CODE_NO_IMPRINT = 'NO_IMPRINT';
    const CC_CODE_NOT_PRESENT = 'NOT_PRESENT';

    /**
     * Transaction recurring values
     */
    const TRANSACT_RECURRING_YES = 'Yes';
    const TRANSACT_RECURRING_NO = 'No';

    /**
     * Transaction origin values
     */
    const TRANSACT_ORIGIN_ECI = 'ECI'; //email or Internet
    const TRANSACT_ORIGIN_MOTO = 'MOTO'; //mail order / telephone order
    const TRANSACT_ORIGIN_RETAIL = 'RETAIL'; //face to face

    /**
     * LinkPoint constructor.
     *
     * @param string     $username
     * @param string     $password
     * @param string     $clientCertDir - Certificates should be named after your login
     * @param            $clientCertPassword
     * @param bool|false $debug
     */
    public function __construct(
        $username,
        $password,
        $clientCertDir,
        $clientCertPassword,
        $debug = false
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->clientCertDir = $clientCertDir;
        $this->clientCertPassword = $clientCertPassword;
        $this->setTestMode((bool)$debug);
    }

    /**
     * @param $value
     */
    public function setTestMode($value)
    {
        self::$testMode = (bool)$value;
    }

    /**
     * set the api username we are going to use
     *
     * @param string $username - the api username
     * @return object
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * set the api password we are going to use
     *
     * @param string $password - the api password
     * @return object
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the folder with SSL certificate
     *
     * @return string
     */
    public function getClientCertDir()
    {
        return $this->clientCertDir;
    }

    /**
     * Set the folder with SSL certificate
     *
     * @param string $clientCertDir
     */
    public function setClientCertDir($clientCertDir)
    {
        $this->clientCertDir = $clientCertDir;
    }

    /**
     * @return null
     */
    public function getClientCertPassword()
    {
        return $this->clientCertPassword;
    }

    /**
     * @param null $clientCertPassword
     */
    public function setClientCertPassword($clientCertPassword)
    {
        $this->clientCertPassword = $clientCertPassword;
    }

    /**
     * Get the response data
     *
     * @return mixed the response data
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the response
     *
     * @param mixed $response - response returned from the call
     * @return object
     */
    protected function setResponse($response = '')
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the error code number
     *
     * @return integer error code number
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set the error code number
     *
     * @param integer $code
     * @return object
     */
    public function setErrorCode($code = 0)
    {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Get the error code message
     *
     * @return string error code message
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set the error message
     *
     * @param string $message
     * @return object
     */
    public function setErrorMessage($message = '')
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the headers
     *
     * @param array|string $headers
     * @return object
     */
    protected function setHeaders($headers = '')
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Return the post data fields as an array
     *
     * @return array
     */
    public function getPostData()
    {
        return $this->postFields;
    }

    /**
     * Set post fields
     *
     * @param mixed $key
     * @param mixed $value
     * @return object
     */
    public function setPostData($key, $value = null)
    {
        if (is_array($key) && !$value) {
            foreach ($key as $k => $v) {
                $this->postFields[$k] = $v;
            }
        } elseif (is_array($value) && $key) {
            foreach ($value as $k => $v) {
                $this->postFields[$key][$k] = $v;
            }
        } else {
            $this->postFields[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the total amount of a transaction
     *
     * @return float
     */
    public function getChargeTotal()
    {
        return $this->chargeTotal;
    }

    /**
     * Set the total transaction amount, including tax, VAT, and shipping amounts.
     * The number of positions after the decimal point must not exceed 2.
     * 3.123 is invalid. 3.12, 3.1, and 3 are valid.
     * For sale transaction type, can be $0.00.
     *
     * @param double $total
     * @return object
     */
    public function setChargeTotal($total)
    {
        $total = round(floatval($total), 2);
        $this->chargeTotal = $total;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set the transaction type.
     *
     * @param $type
     * @return $this
     * @throws \Exception
     */
    public function setTransactionType($type)
    {
        $allowedValues = array(
            self::TRAN_SALE,
            self::TRAN_FORCETICKET,
            self::TRAN_PREAUTH,
            self::TRAN_POSTAUTH,
            self::TRAN_RETURN,
            self::TRAN_CREDIT,
            self::TRAN_VOID,
        );

        if (in_array($type, $allowedValues)) {
            $this->transactionType = $type;
        } else {
            throw new \Exception("Invalid value for Transaction Recurring.\n"
                .'Valid values are: '.implode(',', $allowedValues));

        }

        return $this;
    }

    /**
     * Set the customer’s credit card number.
     * The string contains only digits;
     * passing the number in the format xxxx-xxxx-xxxx- xxxx will result in an error.
     * Required if TackData not set
     *
     * @param string $number
     * @return object
     */
    public function setCreditCardNumber($number)
    {
        $this->setPostData('CreditCardData', array('CardNumber' => (string)$number));

        return $this;
    }

    /**
     * Set the expiration month of the customer’s credit card.
     * The content of this element always contains two digits,
     * for example, use 07 for July.
     * Required if TackData not set
     *
     * @param string $month
     * @return object
     * @throws \Exception
     */
    public function setCreditCardExpirationMonth($month)
    {
        if (!is_numeric($month) or (strlen($month) > 2)) {
            throw new \Exception('Credit card expiration month should always contain two digits, for example, use 07 for July');
        }

        $this->setPostData('CreditCardData', array('ExpMonth' => (string)$month));

        return $this;
    }

    /**
     * Set the expiration year of the customer’s credit card.
     * The content of this element always contains two digits,
     * for example, use 09 for 2009.
     * Required if TackData not set
     *
     * @param string $year
     * @return object
     * @throws \Exception
     */
    public function setCreditCardExpirationYear($year)
    {
        if (!is_numeric($year) or (strlen($year) > 2)) {
            throw new \Exception('Credit card expiration year should always contain two digits, for example, use 09 for 2009');
        }

        $this->setPostData('CreditCardData', array('ExpYear' => (string)$year));

        return $this;
    }

    /**
     * Set the three (3) or four (4) digit card security code (CSC),
     * card verification value (CVV) or code (CVC),
     * which is typically printed on the back of the credit card.
     * For information about using CSC, contact support.
     *
     * @param string $code
     * @return object
     */
    public function setCreditCardSecurityCode($code)
    {
        $this->setPostData('CreditCardData', array('CardCodeValue' => (string)$code));

        return $this;
    }

    /**
     * Set the credit card code indicator.
     * It indicates why the card code value was not provided.
     *
     * @param string $indicator
     * @return object
     * @throws \Exception
     */
    public function setCreditCardCodeIndicator($indicator)
    {
        $allowedValues = array(
            self::CC_CODE_NOT_PROVIDED,
            self::CC_CODE_PROVIDED,
            self::CC_CODE_ILLEGIBLE,
            self::CC_CODE_NO_IMPRINT,
            self::CC_CODE_NOT_PRESENT,
        );

        if (in_array($indicator, $allowedValues)) {
            $this->setPostData('CreditCardData', array('CardCodeIndicator' => $indicator));
        } else {
            throw new \Exception("Invalid value for Card Code Indicator.\n"
                .'Valid values are: '.implode(', ', $allowedValues));
        }

        return $this;
    }

    /**
     * Set the track data of a card when using a card reader instead of keying in card data.
     * Use this value instead CardNumber, ExpMonth and ExpYear when swiping the card.
     * This field needs to contain either track 1 data, track 2 data, or concatenated track 1 and 2 data.
     * Concatenated track data must include the track and field separators, as they are stored on the card.
     * Track 1 and track 2 data are in the format: %<track 1>?;<track 2>?
     *
     * @param string $indicator
     * @return object
     */
    public function setCreditCardTrackData($indicator)
    {
        $this->setPostData('CreditCardData', array('TrackData' => (string)$indicator));

        return $this;
    }

    /**
     * The two-digit PayerSecurityLevel returned by your Merchant Plug-in.
     *
     * @param string $payerSecurityLevel
     * @return $this
     */
    public function setCreditCard3DSecPayerSecurity($payerSecurityLevel)
    {
        $this->setPostData('CreditCard3DSecure', array('PayerSecurityLevel' => (string)$payerSecurityLevel));

        return $this;
    }

    /**
     * The AuthenticationValue (MasterCard: AAV or VISA: CAAV) returned by your Merchant Plug-in.
     *
     * @param string $authValue
     * @return $this
     */
    public function setCreditCard3DSecAuthenticationValue($authValue)
    {
        $this->setPostData('CreditCard3DSecure', array('AuthenticationValue' => (string)$authValue));

        return $this;
    }

    /**
     * The XID returned by your Merchant Plug-in.
     *
     * @param string $XID
     * @return $this
     */
    public function setCreditCard3DSecXID($XID)
    {
        $this->setPostData('CreditCard3DSecure', array('XID' => (string)$XID));

        return $this;
    }

    /**
     * Alias function for setChargeTotal()
     *
     * @param double $total
     * @return object
     */
    public function setPaymentTotal($total)
    {
        return $this->setChargeTotal($total);
    }

    /**
     * Set the sub total amount of the transaction, not including tax, VAT, or shipping amounts.
     *
     * @param double $subTotal
     * @return object
     */
    public function setPaymentSubTotal($subTotal)
    {
        $subTotal = round(floatval($subTotal), 2);
        $this->setPostData('Payment', array('SubTotal' => $subTotal));

        return $this;
    }

    /**
     * Set the tax amount of the transaction.
     *
     * @param double $tax
     * @return object
     */
    public function setPaymentTax($tax)
    {
        $tax = round(floatval($tax), 2);
        $this->setPostData('Payment', array('Tax' => $tax));

        return $this;
    }

    /**
     * Set the VAT tax amount
     *
     * @param double $VATTax
     * @return object
     */
    public function setPaymentVATTax($VATTax)
    {
        $VATTax = round(floatval($VATTax), 2);
        $this->setPostData('Payment', array('VATTax' => $VATTax));

        return $this;
    }

    /**
     * Set the shipping amount of the transaction
     *
     * @param double $shipping
     * @return object
     */
    public function setPaymentShipping($shipping)
    {
        $shipping = round(floatval($shipping), 2);
        $this->setPostData('Payment', array('Shipping' => $shipping));

        return $this;
    }

    /**
     * User ID of the user who performed the transaction. This value is used for reporting.
     *
     * @param $userId
     * @return $this
     */
    public function setTransactionUserId($userId)
    {
        $this->setPostData('TransactionDetails', array('UserID' => (string)$userId));

        return $this;
    }

    /**
     * Invoice number assigned by the merchant.
     *
     * @param string $invoiceNum
     * @return $this
     */
    public function setTransactionInvoiceNumber($invoiceNum)
    {
        $this->setPostData('TransactionDetails', array('InvoiceNumber' => (string)$invoiceNum));

        return $this;
    }

    /**
     * Order ID This must be unique for the Store ID. If no Order ID is transmitted,
     * the Web Service API assigns a value.
     * The First Data Global Gateway Web Service API only accepts ASCII characters.
     * The Order ID cannot contain the following characters: &, %, /,
     * or exceed 100 characters in length.
     * The Order ID will be restricted in such a way so that it can only accepts alpha numeric (a-z, A-Z, 0-9)
     * and some special characters for merchants convenience.
     * The allowed special characters are Hash(#), Underscore( _ ), Hyphen(-), Attherate(@), Dot(.), Colon(:), Space( )
     *
     * @param string $orderId
     * @return $this
     * @throws \Exception
     */
    public function setTransactionOrderId($orderId)
    {
        if (strlen($orderId) > 100) {
            throw new \Exception('The Order ID cannot exceed 100 characters in length');
        }

        $ptrn = '/[\&\%\/]/';
        preg_match($ptrn, $orderId, $matches);
        if (!empty($matches)) {
            throw new \Exception('The Order ID cannot contain the following characters: &, %, /');
        }

        $this->setPostData('TransactionDetails', array('OrderId' => (string)$orderId));

        return $this;
    }

    /**
     * Customer’s IP address which can be used by the Web Service API for fraud detection by IP address.
     * Must be in the format xxx.xxx.xxx.xxx, for example 128.0.10.2 is a valid IP.
     *
     * @param string $ipAddr
     * @return $this
     */
    public function setTransactionIp($ipAddr)
    {
        $this->setPostData('TransactionDetails', array('Ip' => (string)$ipAddr));

        return $this;
    }

    /**
     * The six (6) digit reference number received as the result of a successful external authorization (for example, by phone).
     * This value is required for mapping a ForceTicket transaction to a previous authorization.
     *
     * @param string $refNum
     * @return $this
     */
    public function setTransactionReferenceNumber($refNum)
    {
        $this->setPostData('TransactionDetails', array('ReferenceNumber' => (string)$refNum));

        return $this;
    }

    /**
     * The TDate of the Sale, PostAuth, ForceTicket, Return, or Credit transaction referred to by a Void transaction.
     * The TDate value is returned in the response to a successful transaction.
     * When performing a Void transaction, the TDate and OrderId of the original transaction are required.
     *
     * @param string $tDate
     * @return $this
     */
    public function setTransactionTDate($tDate)
    {
        $this->setPostData('TransactionDetails', array('TDate' => (string)$tDate));

        return $this;
    }

    /**
     * Indicates if the transaction is a recurring transaction.
     * This is a required field and must contain a value: Yes or No.
     *
     * @param string $recurring
     * @return $this
     * @throws \Exception
     */
    public function setTransactionRecurring($recurring)
    {
        $allowedValues = array(
            self::TRANSACT_RECURRING_YES,
            self::TRANSACT_RECURRING_NO,
        );

        if (in_array($recurring, $allowedValues)) {
            $this->transactionRecurring = $recurring;
        } else {
            throw new \Exception("Invalid value for Transaction Recurring.\n"
                .'Valid values are: '.implode(', ', $allowedValues));
        }

        return $this;
    }

    /**
     * Return transaction recurring state
     *
     * @return string
     */
    public function getTransactionRecurring()
    {
        return $this->transactionRecurring;
    }

    /**
     * Indicates if the transaction is exempt from tax. Valid values are: Yes or No
     *
     * @param $taxExempt
     * @return $this
     * @throws \Exception
     */
    public function setTransactionTaxExempt($taxExempt)
    {
        $allowedValues = array(
            'Yes',
            'No',
        );

        if (in_array($taxExempt, $allowedValues)) {
            $this->setPostData('TransactionDetails', array('TaxExempt' => (string)$taxExempt));
        } else {
            throw new \Exception("Invalid value for Transaction Details.\n"
                .'Valid values are: '.implode(', ', $allowedValues));
        }

        return $this;
    }

    /**
     * The type of the terminal performing the transaction, up to 32 characters.
     * Valid values are:
     *  Standalone – point-of-sale credit card terminal
     *  POS – electronic cash register or integrated POS system
     *  Unattended – self-service station
     *  Unspecified – e-commerce, general, CRT, or other applications
     *
     * @param $terminalType
     * @return $this
     * @throws \Exception
     */
    public function setTransactionTerminalType($terminalType)
    {
        $allowedValues = array(
            'Standalone',
            'POS',
            'Unattended',
            'Unspecified',
        );

        if (in_array($terminalType, $allowedValues)) {
            $this->setPostData('TransactionDetails', array('TaxExempt' => (string)$terminalType));
        } else {
            throw new \Exception("Invalid value for Transaction Details.\n"
                .'Valid values are: '.implode(', ', $allowedValues));
        }

        return $this;
    }

    /**
     * Required: The source of the transaction.
     * Valid values are:
     *  ECI - email or Internet
     *  MOTO - mail order / telephone order
     *  RETAIL - face to face
     *
     * @param string $origin
     * @return $this
     * @throws \Exception
     */
    public function setTransactionOrigin($origin)
    {
        $allowedValues = array(
            self::TRANSACT_ORIGIN_ECI,
            self::TRANSACT_ORIGIN_MOTO,
            self::TRANSACT_ORIGIN_RETAIL,
        );

        if (in_array($origin, $allowedValues)) {
            $this->transactionOrigin = $origin;
        } else {
            throw new \Exception("Invalid value for Transaction Origin.\n"
                .'Valid values are: '.implode(', ', $allowedValues));
        }

        return $this;
    }

    /**
     * Return transaction origin
     *
     * @return string
     */
    public function getTransactionOrigin()
    {
        return $this->transactionOrigin;
    }

    /**
     * The purchase order number of the transaction, if applicable.
     *
     * @param $PONumber
     * @return $this
     */
    public function setTransactionPONumber($PONumber)
    {
        $this->setPostData('TransactionDetails', array('PONumber' => (string)$PONumber));

        return $this;
    }

    /**
     * Data to help identify potential fraud on the consumer’s computer
     *
     * @param $deviceId
     * @return $this
     */
    public function setTransactionDeviceID($deviceId)
    {
        $this->setPostData('TransactionDetails', array('DeviceID' => (string)$deviceId));

        return $this;
    }


    /**
     * Merchant’s ID for the customer.
     *
     * @param $customerId
     * @return $this
     */
    public function setBillingCustomerID($customerId)
    {
        $this->setPostData('Billing', array('CustomerID' => (string)$customerId));

        return $this;
    }

    /**
     * Customer’s Name - If provided, it will appear on your transaction reports.
     * MOTO & ECI: Required; Retail: Optional
     *
     * @param string $name
     * @return $this
     */
    public function setBillingName($name)
    {
        $this->setPostData('Billing', array('Name' => (string)$name));

        return $this;
    }

    /**
     * Customer’s company. If provided, it will appear on your transaction reports.
     *
     * @param string $company
     * @return $this
     */
    public function setBillingCompany($company)
    {
        $this->setPostData('Billing', array('Company' => (string)$company));

        return $this;
    }

    /**
     * The first line of the customer’s address. If provided, it will appear on your transaction reports.
     * MOTO & ECI: Required Retail: Optional
     *
     * @param string $address
     * @return $this
     */
    public function setBillingAddress1($address)
    {
        $this->setPostData('Billing', array('Address1' => (string)$address));

        return $this;
    }

    /**
     * The second line of the customer’s address. If provided, it will appear on your transaction reports.
     *
     * @param string $address
     * @return $this
     */
    public function setBillingAddress2($address)
    {
        $this->setPostData('Billing', array('Address2' => (string)$address));

        return $this;
    }

    /**
     * Customer’s city. If provided, it will appear on your transaction reports.
     * MOTO & ECI: Required Retail: Optional
     *
     * @param string $city
     * @return $this
     */
    public function setBillingCity($city)
    {
        $this->setPostData('Billing', array('City' => (string)$city));

        return $this;
    }

    /**
     * Customer’s state - If provided, it will appear on your transaction reports.
     * MOTO & ECI: Required Retail: Optional
     *
     * @param string $state
     * @return $this
     */
    public function setBillingState($state)
    {
        $this->setPostData('Billing', array('State' => (string)$state));

        return $this;
    }

    /**
     * Customer’s ZIP code - If provided, it will appear on your transaction reports.
     * MOTO & ECI: Required Retail: Optional
     *
     * @param string $zip
     * @return $this
     */
    public function setBillingZip($zip)
    {
        $this->setPostData('Billing', array('Zip' => (string)$zip));

        return $this;
    }

    /**
     * Customer’s country - If provided, it will appear on your transaction reports.
     * MOTO & ECI: Required Retail: Optional
     *
     * @param string $country
     * @return $this
     */
    public function setBillingCountry($country)
    {
        $this->setPostData('Billing', array('Country' => (string)$country));

        return $this;
    }

    /**
     * Customer’s phone number - If provided, it will appear on your transaction reports.
     *
     * @param string $phone
     * @return $this
     */
    public function setBillingPhone($phone)
    {
        $this->setPostData('Billing', array('Phone' => (string)$phone));

        return $this;
    }

    /**
     * Customer’s fax number - If provided, it will appear on your transaction reports.
     *
     * @param string $fax
     * @return $this
     */
    public function setBillingFax($fax)
    {
        $this->setPostData('Billing', array('Fax' => (string)$fax));

        return $this;
    }

    /**
     * Customer’s email address - If provided, it will appear on your transaction reports.
     * Optional, But is required to have receipts emailed to customer and administrator
     *
     * @param string $email
     * @return $this
     */
    public function setBillingEmail($email)
    {
        $this->setPostData('Billing', array('Email' => (string)$email));

        return $this;
    }

    /**
     * Shipping Method
     *
     * @param string $type
     * @return $this
     */
    public function setShippingType($type)
    {
        $this->setPostData('Shipping', array('Type' => (string)$type));

        return $this;
    }

    /**
     * Recipient’s name
     * If provided, it will appear on your transaction reports.
     *
     * @param string $name
     * @return $this
     */
    public function setShippingName($name)
    {
        $this->setPostData('Shipping', array('Name' => (string)$name));

        return $this;
    }

    /**
     * The first line of the shipping address.
     * If provided, it will appear on your transaction reports.
     *
     * @param string $address
     * @return $this
     */
    public function setShippingAddress1($address)
    {
        $this->setPostData('Shipping', array('Address1' => (string)$address));

        return $this;
    }

    /**
     * The second line of the shipping address.
     * If provided, it will appear on your transaction reports.
     *
     * @param string $address
     * @return $this
     */
    public function setShippingAddress2($address)
    {
        $this->setPostData('Shipping', array('Address2' => (string)$address));

        return $this;
    }

    /**
     * Recipient’s city
     * If provided, it will appear on your transaction reports.
     *
     * @param string $city
     * @return $this
     */
    public function setShippingCity($city)
    {
        $this->setPostData('Shipping', array('City' => (string)$city));

        return $this;
    }

    /**
     * Recipient’s state
     * If provided, it will appear on your transaction reports.
     *
     * @param string $state
     * @return $this
     */
    public function setShippingState($state)
    {
        $this->setPostData('Shipping', array('State' => (string)$state));

        return $this;
    }

    /**
     * Recipient’s ZIP Code - If provided, it will appear on your transaction reports.
     *
     * @param string $zip
     * @return $this
     */
    public function setShippingZip($zip)
    {
        $this->setPostData('Shipping', array('Zip' => (string)$zip));

        return $this;
    }

    /**
     * Recipient’s country - If provided, it will appear on your transaction reports.
     *
     * @param string $country
     * @return $this
     */
    public function setShippingCountry($country)
    {
        $this->setPostData('Shipping', array('Country' => (string)$country));

        return $this;
    }

    /**
     * Integer code defined by the merchant identifying the carrier type
     *
     * @param string $carrier
     * @return $this
     */
    public function setShippingCarrier($carrier)
    {
        $this->setPostData('Shipping', array('Carrier' => (string)$carrier));

        return $this;
    }

    /**
     * The transaction amount prior to calculating shipping.
     * The number of positions after the decimal point must not exceed 2.
     * 3.123 is invalid. 3.12, 3.1, and 3 are valid.
     *
     * @param double $total
     * @return $this
     */
    public function setShippingTotal($total)
    {
        $total = round(floatval($total), 2);
        $this->setPostData('Shipping', array('Total' => $total));

        return $this;
    }

    /**
     * The weight of the item shipped, in pounds or kilograms as determined by the merchant.
     *
     * @param double $weight
     * @return $this
     */
    public function setShippingWeight($weight)
    {
        $this->setPostData('Shipping', array('Weight' => floatval($weight)));

        return $this;
    }

    protected function getRequestContent()
    {
        //we need to keep specific order for xml tags
        $sections = array(
            'CreditCardTxType',
            'CreditCardData',
            'CreditCard3DSecure',
            'Payment',
            'TransactionDetails',
            'Billing',
            'Shipping',
        );

        $result = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Header />
<SOAP-ENV:Body>
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1" xmlns:fdggwsapi= "http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">
<v1:Transaction>'."\n";

        foreach ($sections as $section) {
            $result .= $this->getRequestContentSection($section);
        }

        $result .= '</v1:Transaction>
</fdggwsapi:FDGGWSApiOrderRequest>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

        return $result;
    }

    protected function getRequestContentSection($sectionName)
    {
        $result = '';
        $postData = $this->getPostData();

        switch ($sectionName) {
            case 'CreditCardTxType':
                $postData[$sectionName]['Type'] = $this->getTransactionType();
                break;
            case 'Payment':
                $postData[$sectionName]['ChargeTotal'] = $this->getChargeTotal();
                break;
            case 'TransactionDetails':
                $postData[$sectionName]['Recurring'] = $this->getTransactionRecurring();
                $postData[$sectionName]['TransactionOrigin'] = $this->getTransactionOrigin();
                break;
        }

        if (isset($postData[$sectionName]) and !empty($postData[$sectionName])) {
            $result .= '<v1:'.$sectionName.'>'."\n";

            if (is_array($postData[$sectionName])) {
                foreach ($postData[$sectionName] as $k => $v) {
                    $result .= '<v1:'.$k.'>'.$v.'</v1:'.$k.'>'."\n";
                }
            } else {
                $result .= '<v1:'.$sectionName.'>'.$postData[$sectionName].'</v1:'.$sectionName.'>'."\n";
            }

            $result .= '</v1:'.$sectionName.'>'."\n";
        }

        return $result;
    }

    /**
     * Perform the API call
     *
     * @return string
     */
    public function process()
    {
        return $this->doRequest();
    }

    protected function doRequest($ch = null)
    {
        if (!$ch) {
            $ch = curl_init();
        }

        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = self::$testMode ? self::TEST_API_URL : self::LIVE_API_URL;
        $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $opts[CURLOPT_USERPWD] = $this->username.':'.$this->password;
        $opts[CURLOPT_POSTFIELDS] = $this->getRequestContent();

        // setting the path where cURL can find the client certificate:
        $opts[CURLOPT_SSLCERT] = $this->getClientCertDir().$this->username.'.pem';
        // setting the path where cURL can find the client certificate’s private key:
        $opts[CURLOPT_SSLKEY] = $this->getClientCertDir().$this->username.'.key';
        // setting the key password:
        $opts[CURLOPT_SSLKEYPASSWD] = $this->getClientCertPassword();

        curl_setopt_array($ch, $opts);

        // execute
        $this->setResponse(curl_exec($ch));
        $this->setHeaders(curl_getinfo($ch));

        // fetch errors
        $this->setErrorCode(curl_errno($ch));
        $this->setErrorMessage(curl_error($ch));

        if ($this->isError() and ($this->getResponseErrorMessage() !== false)) {
            $this->setErrorMessage($this->getResponseErrorMessage());
        }

        // close
        curl_close($ch);

        // Reset
        $this->postFields = array();

        return $this->getResponse();
    }

    /**
     * Did we encounter an error?
     *
     * @return boolean
     */
    public function isError()
    {
        $headers = $this->getHeaders();
        $response = $this->getResponse();

        // First make sure we got a valid response
        if (!in_array($headers['http_code'], array(200, 201, 202))) {
            return true;
        }

        // Do we have an error code
        if ($this->getErrorCode() > 0) {
            return true;
        }

        // Make sure the response does not have error in it
        if (!$response) {
            return true;
        } elseif (!is_null($this->getResponseErrorMessage())) {
            return true;
        }

        // No error
        return false;
    }

    /**
     * Parse response and return the exact
     *
     * @return null|string - Error message or null when no error found
     */
    protected function getResponseErrorMessage()
    {
        $response = $this->getResponse();

        $xml = simplexml_load_string($response, null, null, "http://schemas.xmlsoap.org/soap/envelope/");
        $ns = $xml->getNamespaces(true);
        $responseBody = $xml->children($ns['SOAP-ENV'])->Body;

        if (isset($responseBody->Fault)) {
            return $responseBody->Fault->faultstring->__toString();
        } elseif (isset($responseBody->children($ns['fdggwsapi'])->FDGGWSApiOrderResponse)) {
            $responseBody = $responseBody->children($ns['fdggwsapi'])->FDGGWSApiOrderResponse;
            if ($responseBody->TransactionResult != 'APPROVED') {
                return $responseBody->TransactionResult->__toString()
                .': '
                .$responseBody->ErrorMessage->__toString();
            }
        }

        return null;
    }

    /**
     * Was the last call successful?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return !$this->isError() ? true : false;
    }

}
