<?php
require __DIR__.'/vendor/autoload.php';

require 'Nmi3Step.php';

// Test API key taken from documentation
define('NMI_KEY','2F822Rw39fx762MaV7Yy86jXGTC7sCDy');
$nmi=new Nmi3Step(NMI_KEY);
// $nmi->set_debug(true); // uncomment this to expose dumps of sent and received XML data

set_exception_handler(function(Exception $e){
	echo '<h3>An exception occurred</h3>';
	echo '<p><em>'.$e->getMessage().'</em></p>';
});

if (!empty($_GET['token-id'])){
	$payment=$nmi->submit_payment($_GET['token-id']);

	echo '<h3>Transaction approved</h3>';
	echo '<p>The code for this transaction is "<strong>'.$payment->{'transaction-id'}.'</strong>"</p>';
	echo '<p><a href="'.$_SERVER['PHP_SELF'].'">Go again</a></p>';
}
else {
	$amount=5;
	$form_url=$nmi->get_url($amount);

	$html='<form action="'.$form_url.'" method="post">
		<fieldset class="align">
			<div><label>Num</label> <input type="text" name="billing-cc-number" placeholder="Num" value="5431111111111111"></div>
			<div><label>Exp</label> <input type="text" name="billing-cc-exp" placeholder="Exp" class="short" value="10/10"></div>
			<div><label>CVV</label> <input type="text" name="billing-cvv" placeholder="CVV" class="short" value="111"></div>
			<input type="submit" value="Submit to NMI" class="submit">
		</fieldset>
	</form>';

	echo $html;
}