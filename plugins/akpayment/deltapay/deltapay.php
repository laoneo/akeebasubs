<?php
/**
 * @package		akeebasubs
 * @copyright	Copyright (c)2010-2012 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

class plgAkpaymentDeltapay extends JPlugin
{
	/**
	 * Maps currency three letter codes to number, as per ISO 4217
	 */
	private $currencyMap = array(
		'AED' => 784, 'AFN' => 971, 'ALL' => 8, 'AMD' => 51, 'ANG' => 532, 'AOA' => 973,
		'ARS' => 32, 'AUD' => 36, 'AWG' => 533, 'AZN' => 944, 'BAM' => 977, 'BBD' => 52,
		'BDT' => 50, 'BGN' => 975, 'BHD' => 48, 'BIF' => 108, 'BMD' => 60, 'BND' => 96,
		'BOB' => 68, 'BOV' => 984, 'BRL' => 986, 'BSD' => 44, 'BTN' => 64, 'BWP' => 72,
		'BYR' => 974, 'BZD' => 84, 'CAD' => 124, 'CDF' => 976, 'CHE' => 947, 'CHF' => 756,
		'CHW' => 948, 'CLF' => 990, 'CLP' => 152, 'CNY' => 156, 'COP' => 170, 'COU' => 970,
		'CRC' => 188, 'CUC' => 931, 'CUP' => 192, 'CVE' => 132, 'CZK' => 203, 'DJF' => 262,
		'DKK' => 208, 'DOP' => 214, 'DZD' => 12, 'EGP' => 818, 'ERN' => 232, 'ETB' => 230,
		'EUR' => 978, 'FJD' => 242, 'FKP' => 238, 'GBP' => 826, 'GEL' => 981, 'GHS' => 936,
		'GIP' => 292, 'GMD' => 270, 'GNF' => 324, 'GTQ' => 320, 'GYD' => 328, 'HKD' => 344,
		'HNL' => 340, 'HRK' => 191, 'HTG' => 332, 'HUF' => 348, 'IDR' => 360, 'ILS' => 376,
		'INR' => 356, 'IQD' => 368, 'IRR' => 364, 'ISK' => 352, 'JMD' => 388, 'JOD' => 400,
		'JPY' => 392, 'KES' => 404, 'KGS' => 417, 'KHR' => 116, 'KMF' => 174, 'KPW' => 408,
		'KRW' => 410, 'KWD' => 414, 'KYD' => 136, 'KZT' => 398, 'LAK' => 418, 'LBP' => 422,
		'LKR' => 144, 'LRD' => 430, 'LSL' => 426, 'LTL' => 440, 'LVL' => 428, 'LYD' => 434,
		'MAD' => 504, 'MDL' => 498, 'MGA' => 969, 'MKD' => 807, 'MMK' => 104, 'MNT' => 496,
		'MOP' => 446, 'MRO' => 478, 'MUR' => 480, 'MVR' => 462, 'MWK' => 454, 'MXN' => 484,
		'MXV' => 979, 'MYR' => 458, 'MZN' => 943, 'NAD' => 516, 'NGN' => 566, 'NIO' => 558,
		'NOK' => 578, 'NPR' => 524, 'NZD' => 554, 'OMR' => 512, 'PAB' => 590, 'PEN' => 604,
		'PGK' => 598, 'PHP' => 608, 'PKR' => 586, 'PLN' => 985, 'PYG' => 600, 'QAR' => 634,
		'RON' => 946, 'RSD' => 941, 'RUB' => 643, 'RWF' => 646, 'SAR' => 682, 'SBD' => 90,
		'SCR' => 690, 'SDG' => 938, 'SEK' => 752, 'SGD' => 702, 'SHP' => 654, 'SLL' => 694,
		'SOS' => 706, 'SRD' => 968, 'SSP' => 728, 'STD' => 678, 'SYP' => 760, 'SZL' => 748,
		'THB' => 764, 'TJS' => 972, 'TMT' => 934, 'TND' => 788, 'TOP' => 776, 'TRY' => 949,
		'TTD' => 780, 'TWD' => 901, 'TZS' => 834, 'UAH' => 980, 'UGX' => 800, 'USD' => 840,
		'USN' => 997, 'USS' => 998, 'UYI' => 940, 'UYU' => 858, 'UZS' => 860,  'VEF' => 937,
		'VND' => 704, 'VUV' => 548, 'WST' => 882, 'XXX' => 999, 'YER' => 886, 'ZAR' => 710,
		'ZMK' => 894, 'ZWL' => 932,
	);
	
	private $ppName = 'deltapay';
	private $ppKey = 'PLG_AKPAYMENT_DELTAPAY_TITLE';

	public function __construct(&$subject, $config = array())
	{
		if(!version_compare(JVERSION, '1.6.0', 'ge')) {
			if(!is_object($config['params'])) {
				$config['params'] = new JParameter($config['params']);
			}
		}
		parent::__construct($subject, $config);
		
		require_once JPATH_ADMINISTRATOR.'/components/com_akeebasubs/helpers/cparams.php';
		
		// Load the language files
		$jlang = JFactory::getLanguage();
		$jlang->load('plg_akpayment_deltapay', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_akpayment_deltapay', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_akpayment_deltapay', JPATH_ADMINISTRATOR, null, true);
	}

	public function onAKPaymentGetIdentity()
	{
		$title = $this->params->get('title','');
		if(empty($title)) $title = JText::_($this->ppKey);
		$ret = array(
			'name'		=> $this->ppName,
			'title'		=> $title
		);
		return (object)$ret;
	}
	
	/**
	 * Returns the payment form to be submitted by the user's browser. The form must have an ID of
	 * "paymentForm" and a visible submit button.
	 * 
	 * @param string $paymentmethod
	 * @param JUser $user
	 * @param AkeebasubsTableLevel $level
	 * @param AkeebasubsTableSubscription $subscription
	 * @return string
	 */
	public function onAKPaymentNew($paymentmethod, $user, $level, $subscription)
	{
		if($paymentmethod != $this->ppName) return false;
		
		$nameParts = explode(' ', $user->name, 2);
		$firstName = $nameParts[0];
		if(count($nameParts) > 1) {
			$lastName = $nameParts[1];
		} else {
			$lastName = '';
		}
		
		$slug = FOFModel::getTmpInstance('Levels','AkeebasubsModel')
				->setId($subscription->akeebasubs_level_id)
				->getItem()
				->slug;
		
		$rootURL = rtrim(JURI::base(),'/');
		$subpathURL = JURI::base(true);
		if(!empty($subpathURL) && ($subpathURL != '/')) {
			$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
		}
		
		$currency = strtoupper(AkeebasubsHelperCparams::getParam('currency','EUR'));
		if(array_key_exists($currency, $this->currencyMap)) {
			$currencyCode = $this->currencyMap[$currency];
		} else {
			$currencyCode = 999;
		}
		
		$data = (object)array(
			'url'			=> 'https://www.deltapay.gr/entry.asp',
			'merchant'		=> $this->params->get('merchant',''),
			//'postback'		=> rtrim(JURI::base(),'/').str_replace('&amp;','&',JRoute::_('index.php?option=com_akeebasubs&view=callback&paymentmethod=deltapay')),
			'postback'		=> JURI::base().'index.php?option=com_akeebasubs&view=callback&paymentmethod=deltapay',
			'success'		=> $rootURL.str_replace('&amp;','&',JRoute::_('index.php?option=com_akeebasubs&view=message&slug='.$slug.'&layout=order&subid='.$subscription->akeebasubs_subscription_id)),
			'cancel'		=> $rootURL.str_replace('&amp;','&',JRoute::_('index.php?option=com_akeebasubs&view=message&slug='.$slug.'&layout=cancel&subid='.$subscription->akeebasubs_subscription_id)),
			'currency'		=> $currencyCode,
			'firstname'		=> $firstName,
			'lastname'		=> $lastName,
			'charge'		=> str_replace('.',',',sprintf('%02.2f',$subscription->gross_amount))
		);
		
		$kuser = FOFModel::getTmpInstance('Users','AkeebasubsModel')
			->user_id($user->id)
			->getFirstItem();
		
		@ob_start();
		include dirname(__FILE__).'/deltapay/form.php';
		$html = @ob_get_clean();
		
		return $html;
	}
	
	public function onAKPaymentCallback($paymentmethod, $data)
	{
		jimport('joomla.utilities.date');
		
		// Check if we're supposed to handle this
		if($paymentmethod != $this->ppName) return false;
		
		// Wow, there is no IPN check whatsoever! Amazing security level...
		$isValid = true;
		
		// Load the relevant subscription row
		if($isValid) {
			$id = array_key_exists('Param1', $data) ? (int)$data['Param1'] : -1;
			$subscription = null;
			if($id > 0) {
				$subscription = FOFModel::getTmpInstance('Subscriptions','AkeebasubsModel')
					->setId($id)
					->getItem();
				if( ($subscription->akeebasubs_subscription_id <= 0) || ($subscription->akeebasubs_subscription_id != $id) ) {
					$subscription = null;
					$isValid = false;
				}
			} else {
				$isValid = false;
			}
			if(!$isValid) $data['akeebasubs_failure_reason'] = 'The referenced subscription ID ("Param1" field) is invalid';
		}
		
		// Check that the amount is correct
		if($isValid && !is_null($subscription)) {
			$charge = str_replace(',','.',$data['Charge']);
			$mc_gross = floatval($charge);
			$gross = $subscription->gross_amount;
			// Important: NEVER, EVER compare two floating point values for equality.
			$isValid = ($gross - $mc_gross) < 0.01;
			if(!$isValid) $data['akeebasubs_failure_reason'] = 'Paid amount (Charge field) does not match the subscription amount';
		}
		
		// Log the IPN data
		$this->logIPN($data, $isValid);
		
		// Fraud attempt? Do nothing more!
		if(!$isValid) die('Hacking attempt; payment processing refused');
		
		// Load the subscription level and get its slug
		$slug = FOFModel::getTmpInstance('Levels','AkeebasubsModel')
				->setId($subscription->akeebasubs_level_id)
				->getItem()
				->slug;

		// Check the payment_status
		
		$rootURL = rtrim(JURI::base(),'/');
		$subpathURL = JURI::base(true);
		if(!empty($subpathURL) && ($subpathURL != '/')) {
			$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
		}
		
		switch($data['Result'])
		{
			case '1': // Success
				$newStatus = 'C';
				$returnURL = $rootURL.str_replace('&amp;','&',JRoute::_('index.php?option=com_akeebasubs&view=message&layout=default&slug='.$slug.'&layout=order&subid='.$subscription->akeebasubs_subscription_id));
				break;
			
			case '2': // Error
			case '3': // Cancelled
			default:
				$newStatus = 'X';
				$returnURL = $rootURL.str_replace('&amp;','&',JRoute::_('index.php?option=com_akeebasubs&view=message&layout=default&slug='.$slug.'&layout=cancel&subid='.$subscription->akeebasubs_subscription_id));
				break;
		}

		// Update subscription status (this also automatically calls the plugins)
		$updates = array(
			'akeebasubs_subscription_id'				=> $id,
			'processor_key'		=> $data['Param1'],
			'state'				=> $newStatus,
			'enabled'			=> 0
		);
		jimport('joomla.utilities.date');
		if($newStatus == 'C') {
			// Fix the starting date if the payment was accepted after the subscription's start date. This
			// works around the case where someone pays by e-Check on January 1st and the check is cleared
			// on January 5th. He'd lose those 4 days without this trick. Or, worse, if it was a one-day pass
			// the user would have paid us and we'd never given him a subscription!
			$jNow = new JDate();
			$jStart = new JDate($subscription->publish_up);
			$jEnd = new JDate($subscription->publish_down);
			$now = $jNow->toUnix();
			$start = $jStart->toUnix();
			$end = $jEnd->toUnix();
			
			if($start < $now) {
				$duration = $end - $start;
				$start = $now;
				$end = $start + $duration;
				$jStart = new JDate($start);
				$jEnd = new JDate($end);
			}
			
			$updates['publish_up'] = $jStart->toMySQL();
			$updates['publish_down'] = $jEnd->toMySQL();
			$updates['enabled'] = 1;
		}
		$subscription->save($updates);
		
		// Run the onAKAfterPaymentCallback events
		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('akeebasubs');
		$app = JFactory::getApplication();
		$jResponse = $app->triggerEvent('onAKAfterPaymentCallback',array(
			$subscription
		));
		
		$app = JFactory::getApplication();
		$app->redirect($returnURL);
		
		return true;
	}
	
	private function logIPN($data, $isValid)
	{
		$config = JFactory::getConfig();
		$logpath = $config->getValue('log_path');
		$logFile = $logpath.'/akpayment_deltapay_ipn.php';
		jimport('joomla.filesystem.file');
		if(!JFile::exists($logFile)) {
			$dummy = "<?php die(); ?>\n";
			JFile::write($logFile, $dummy);
		} else {
			if(@filesize($logFile) > 1048756) {
				$altLog = $logpath.'/akpayment_deltapay_ipn-1.php';
				if(JFile::exists($altLog)) {
					JFile::delete($altLog);
				}
				JFile::copy($logFile, $altLog);
				JFile::delete($logFile);
				$dummy = "<?php die(); ?>\n";
				JFile::write($logFile, $dummy);
			}
		}
		$logData = JFile::read($logFile);
		if($logData === false) $logData = '';
		$logData .= "\n" . str_repeat('-', 80);
		$logData .= $isValid ? 'VALID DeltaPay IPN' : 'INVALID DeltaPay IPN *** FRAUD ATTEMPT OR INVALID NOTIFICATION ***';
		$logData .= "\nDate/time : ".gmdate('Y-m-d H:i:s')." GMT\n\n";
		foreach($data as $key => $value) {
			$logData .= '  ' . str_pad($key, 30, ' ') . $value . "\n";
		}
		$logData .= "\n";
		JFile::write($logFile, $logData);
	}
}