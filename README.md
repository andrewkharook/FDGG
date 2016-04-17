# FDGG (First Data Global Gateway)
First Data Global Gateway (former LinkPoint) PHP library

## Usage Instructions
First you need to register at https://www.firstdata.com/ and obtain User ID, Password, Certificate files (certificates should be named after your login, for example WS1909538361._.1.pem and WS1909538361._.1.key) and Certificate password.

To initialize the library, you can use something like this in your constructor (make sure you use your credentials and path to certificates directory):
```php
$test_mode = (bool)$_GET['linkpoint_test'];

if ($test_mode) {
  $lp_username = 'WS11111111111._.1';
  $lp_password = 'linkpoint_password_test';
  $clientCertDir = '/var/www/payment/linkpoint/certificates/linkpoint_cert_dir_test';
  $clientCertPassword = 'ckp_linkpoint_certificate_password_test';
} else {
  $lp_username = 'WS11111111112._.1';
  $lp_password = 'linkpoint_password_live';
  $clientCertDir = '/var/www/payment/linkpoint/certificates/linkpoint_cert_dir_live';
  $clientCertPassword = 'ckp_linkpoint_certificate_password_live';
}

$this->LinkPoint = new LinkPoint(
  $lp_username,
  $lp_password,
  $clientCertDir,
  $clientCertPassword,
  $test_mode
);
```

To process the payment, use something like:
```php
try {
  $this->LinkPoint
    ->setCreditCardNumber($_POST['cc_number'])
    ->setCreditCardExpirationMonth($_POST['cc_expdate_month'])
    ->setCreditCardExpirationYear($_POST['cc_expdate_year'])
    ->setCreditCardSecurityCode($_POST['cc_security_code'])
    ->setPaymentTotal($_POST['payment_total'])
    ->setBillingName($_POST['cc_full_name'])
    ->setBillingAddress1($_POST['street'])
    ->setBillingAddress2($_POST['street_2'])
    ->setBillingCity($_POST['city'])
    ->setBillingState($_POST['state'])
    ->setBillingZip($_POST['zip'])
    ->setBillingCountry($_POST['country'])
    ->setBillingPhone($_POST['phone'])
    ->setTransactionUserId($_POST['user_id'])
    ->setTransactionOrderId($_POST['order_id']);
} catch (Exception $e) {
  echo 'Error: ', $e->getMessage(), "\n";
}

$response = $this->LinkPoint->process();

if ($this->LinkPoint->isSuccess()) {
    // Payment successfull.
} elseif (strpos($this->LinkPoint->getErrorMessage(), 'DECLINED:') !== false) {
    // Transaction was declined.
} else {
    // Transaction error. We can get the details by calling $this->LinkPoint->getResponseErrorMessage()
}
```
