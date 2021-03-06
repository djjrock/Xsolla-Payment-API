<?php
require_once 'inc/virtual_currency_protocol.php';
class VirtualCurrencyExample extends VirtualCurrency {
	public function setupDB() {
		try {
			$this->db = new PDO('mysql:host='.DBConfig::HOST.';port='.DBConfig::PORT.';dbname='.DBConfig::DB, DBConfig::USER, DBConfig::PASS);
		} catch (PDOException $e) {
			throw new Exception($e->getMessage()); //verbose exception
			//throw new Exception('could not connect to database.'); //production exception
		}
	}

	public function userExists($user) {
		//query the db to see if user exists
		$sth = $this->db->prepare("SELECT * FROM vc_players WHERE player_id=?");
		$sth->execute(array($user));
		return count($sth->fetchAll()) > 0;
	}

	public function invoiceExists($invoiceID) {
		//query db to see if invoice exists
		$sth = $this->db->prepare("SELECT * FROM vc_payments WHERE payment_invoice=?");
		$sth->execute(array($invoiceID));
		return count($sth->fetchAll()) > 0;
	}

	public function newInvoice($invoiceID, $userID, $sum) {
		try {
			//insert new invoice into db
			$sth = $this->db->prepare("INSERT INTO vc_payments(payment_invoice, user_id, payment_date, payment_total) VALUES (:invoiceID, :userID, now(), :sum)");
			$sth->execute(array(':invoiceID' => $invoiceID, ':userID' => $userID, ':sum' => $sum));
		} catch (PDOException $e) {
			throw new Exception('error creating payment.');
		}
	}

	public function cancelInvoice($invoiceID) {
		//check if order is already canceled
		$sth = $this->db->prepare("SELECT * FROM vc_payments WHERE payment_invoice=? AND payment_canceled=1");
		$sth->execute(array($invoiceID));
		//cancel if this invoice has not been canceled already
		if (count($sth->fetchAll()) < 1) {
			$sth = $this->db->prepare("UPDATE vc_payments SET payment_canceled=1, payment_canceled_date=now() WHERE payment_invoice=?");
			$sth->execute(array($invoiceID));
		}
	}
}

$example = new VirtualCurrencyExample();//var_dump(get_class_methods('VirtualCurrencyExample'));exit;
$example->process();

?>