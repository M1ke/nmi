# NMI API

An implementation of the [NMI](https://secure.networkmerchants.com/gw/merchants/resources/integration/integration_portal.php#integration_overview) (Network Merchants International) payment gateway

### 3-Step

Currently the only implementation. See `example.php` for information on how to use, essentially two main methods are exposed:

	// creates an object, provide API key and optional redirect URL
    $nmi=new Nmi3Step(NMI_KEY);

    // returns a submission url as step 1, send a numeric amount to charge
    $url=$nmi->get_url($amount);

    // Sends the payment with a provided token. Returns a payment result or throws an exception
    $payment=$nmi->submit_payment($_GET['token-id']);

## Installation

Install using [Composer](http://getcomposer.org).

    php composer.phar require m1ke/nmi:dev-x0.1