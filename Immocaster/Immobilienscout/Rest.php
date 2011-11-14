<?php

/**
 * Immocaster SDK
 * Nutzung der ImmobilienScout24 API per REST.
 *
 * @package    Immocaster SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immocaster.com
 * @version    1.1.18
 */

class Immocaster_Immobilienscout_Rest extends Immocaster_Immobilienscout
{
	
	/**
	 * Leseprotokoll: cURL oder standardmäßig mit
	 * PHP-Funktion file_get_contents().
	 */
	 protected $_sUrlReadingType = 'none';
	
	/**
	 * Ergebnisformat: JSON oder standardmäßig XML
	 */
	 protected $_sContentResultType = 'none';

	/**
     * Der Constructor legt die Einstellungen für die 
	 * Verbindung fest und startet diese.
     *
	 * @param string $sKey Key für diesen Service
	 * @param string $sSecret Secret für diesen Service
	 * @param string $sAuth Typ der Authentifizierung für den Service
     * @return void
     */
    public function __construct($sKey,$sSecret,$sAuth)
    {
		parent::connectService($sKey,$sSecret,$sAuth);
    }
	
	/**
     * Protokoll setzen, wie das Result von der
	 * URL gelesen werden soll (z.B. 'none','curl').
     *
	 * @param string $sType Typ wie URLs ausgelesen werden
     * @return boolean
     */
    public function setReadingType($sType='none')
    {
		if(strtolower($sType)=='curl')
		{
			$this->_sUrlReadingType = 'curl';
			return true;
		}
		$this->_sUrlReadingType = 'none';
		return true;
    }
	
	/**
	 * Ergebnisformat setzen (z.B. 'none','json').
	 *
	 * @param string $sContentResultType Formatierung des Ergebnisses
	 * @return boolean
	 */
	public function setContentResultType($sContentResultType='none')
	{
		if(strtolower($sContentResultType)=='json')
		{
			$this->_sContentResultType = 'json';
			return true;
		}
		$this->_sContentResultType = 'none';
		return true;
	}

	/**
     * Haupt-URL für Requests zum Service ändern.
	 *
	 * @param string $sUrl URL oder Keyword um URL zu ändern
	 * @return boolean
     */
	public function setRequestUrl($sUrl)
	{
		if($sUrl==false || $sUrl=='sandbox' || $sUrl=='test')
		{
			$this->_sUri = 'http://sandbox.immobilienscout24.de';
			return true;
		}
		if($sUrl=='live')
		{
			$this->_sUri = 'http://rest.immobilienscout24.de';
			return true;
		}
		$this->_sUri = $sUrl;
		return true;
	}

	/**
     * Magische Funktion welche die Methodenaufrufe
	 * in die jeweilige Funktion der Klasse weiterleitet.
     *
	 * @param string $method
	 * @param array $args
     * @return mixed
     */
	public function __call($method,$args)
	{
		$sMethod = '_'.$method;
		if(method_exists($this,$sMethod))
		{
			return $this->$sMethod(array_change_key_case($args[0],CASE_LOWER));
		}
		return IMMOCASTER_SDK_LANG_FUNCTION_DONT_EXIST;
	}
	
	/**
     * Ausführen des REST Requests (aus den
	 * jeweiligen Funktionen heraus).
     *
	 * @param string $sPath Pfad-Suffix zum Service-Node
	 * @param array $aArgs Parameter für den Request
	 * @param array $aRequired Benötigte Parameter für den Request
	 * @param string $sFunctionName Name der Funktion aus der doRequest aufgerufen wird
	 * @param object $oToken Accesstoken für den 3-Legged-Oauth
     * @return mixed
     */
	private function doRequest($sPath,$aArgs,$aRequired=array(),$sFunctionName,$oToken=null)
	{
		try
		{
			if(parent::requiredArgs($aArgs,$aRequired,$sFunctionName))
			{
				return parent::restRequest($sPath,$aArgs,false,$oToken);
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		return false;
	}
	
	/**
     * Der Geo Service der API liefert die Geo-Struktur
	 * wieder und liefert zu jedem Ort, zu jeder Stadt,
	 * zu jedem Land usw. eine ID anhand der man sich zum
	 * kleinsten Punkt (quarter) durchhangeln kann.
	 * Die Struktur ist Country>Region>City>Quarter.
	 * Wichtig ist der zusätzliche Parameter 'list'. Wenn dieser auf
	 * true gesetzt wird, wird die nächst tiefere Strukturebene 
	 * unterhalb des aktuellen Objekts zurückgegeben. Wenn man
	 * also eine "country-id" angibt und "list" auf true setzt
	 * erhält man alle Regionen des Landes. Wenn man "list" auf false
	 * setzt, bekommt man nur das Land mit ID usw. zurück.
	 *
     * @param array $aArgs Wenn 'list' true übergeben wird,
	 * wird die komplette Liste unterhalb des aktuellen,
	 * Objektes ausgeben.
     * @return mixed
     */
	private function _geoService($aArgs)
	{
		$aRequired=array('list');
		$sSearchQuery = 'gis/v1.0/country';
		if(isset($aArgs['country-id']))
		{
			$sSearchQuery .= '/'.$aArgs['country-id'];
			$iSearchOption = 1;
		}
		if(isset($aArgs['region-id']))
		{
			$sSearchQuery .= '/region/'.$aArgs['region-id'];
			$iSearchOption++;
		}
		if(isset($aArgs['city-id']))
		{
			$sSearchQuery .= '/city/'.$aArgs['city-id'];
			$iSearchOption++;
		}
		if(isset($aArgs['quarter-id']))
		{
			$sSearchQuery .= '/quarter/'.$aArgs['quarter-id'];
			$iSearchOption++;
		}
		if($aArgs['list'])
		{
			switch ($iSearchOption) {
				case 1:
					$sSearchQuery .= '/region';
					break;
				case 2:
					$sSearchQuery .= '/city';
					break;
				case 3:
					$sSearchQuery .= '/quarter';
					break;
			}
		}
		$req = $this->doRequest($sSearchQuery,$aArgs,$aRequired,__FUNCTION__);
		return parent::getContent($req);
	}
	
	/**
     * Abfrage eines Exposes (Objekt)
	 * mit der Objekt-ID.
	 *
     * @param array $aArgs
     * @return mixed
     */
	private function _getExpose($aArgs)
	{
		$aRequired = array('exposeid');
		$oToken = null;
		$sSecret = null;

		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();

		$req = $this->doRequest('search/v1.0/expose/'.$aArgs['exposeid'],$aArgs,$aRequired,__FUNCTION__,$oToken);
		return parent::getContent($req,$sSecret);
	}
	
	/**
     * Abfrage eines Dateianhangs (Attachment).
	 *
     * @param array $aArgs
     * @return mixed
     */
	private function _getAttachment($aArgs)
	{
		$aRequired = array('exposeid');
		$sSearchQuery = 'search/v1.0/expose/'.$aArgs['exposeid'].'/attachment';
		if(isset($aArgs['attachmentid']))
		{
			$sSearchQuery .= '/'.$aArgs['attachmentid'];
		}
		$req = $this->doRequest($sSearchQuery,$aArgs,$aRequired,__FUNCTION__);
		return parent::getContent($req);
	}
	
	/**
     * Abfrage der Geo-Informationen per Stadtname, oder
	 * per Anfangsbuchstaben einer Region.
	 *
     * @param array $aArgs
     * @return mixed
     */
	private function _getRegions($aArgs)
	{
		$aRequired = array('q');
		$req = $this->doRequest('search/v1.0/region',$aArgs,$aRequired,__FUNCTION__);
		return parent::getContent($req);
	}
	
	/**
     * Abfrage von Ergebnislisten anhand von
	 * Geo-Koordinaten und des Objekttyps.
     *
     * @param array $aArgs
     * @return mixed
     */
	private function _radiusSearch($aArgs)
	{
		$aRequired = array('geocoordinates','realestatetype');
		$oToken = null;
		$sSecret = null;
		if(isset($aArgs['username']))
		{
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
			if($oToken === NULL || $sSecret === NULL)
			{
				return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
			}
		}
		$req = $this->doRequest('search/v1.0/search/radius',$aArgs,$aRequired,__FUNCTION__,$oToken);
		return parent::getContent($req,$sSecret);
	}
	
	/**
     * Abfrage von Ergebnislisten anhand der
	 * Region-ID und des Objekttyps.
     *
     * @param array $aArgs
     * @return mixed
     */
	private function _regionSearch($aArgs)
	{
		$aRequired = array('geocodes','realestatetype');
		$oToken = null;
		$sSecret = null;
		if(isset($aArgs['username']))
		{
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
			if($oToken === NULL || $sSecret === NULL)
			{
				return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
			}
		}
		$req = $this->doRequest('search/v1.0/search/region',$aArgs,$aRequired,__FUNCTION__,$oToken);
		return parent::getContent($req,$sSecret);
	}
	
	/**
     * Applikation zeritifizieren.
	 *
     * @param array $aArgs
     * @return mixed
     */
	public function getAccess($aArgs)
	{
		try
		{
			parent::requiredArgs($aArgs,array('verifyApplication'),' '.__FUNCTION__);
			if($aArgs['verifyApplication']!=true){ return false; }
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		if(isset($_GET['state']) && isset($_GET['oauth_token']))
		{
			return $this->registerAccess($aArgs);
		}
		else
		{
			return $this->registerRequest($aArgs);
		}
	}	
	
	/**
     * Applikation Requesttoken ermitteln
	 * und Benutzer auf SSO weiterleiten.
	 *
     * @param array $aArgs
     * @return void
     */
	private function registerRequest($aArgs)
	{
		try
		{
			if(parent::requiredArgs($aArgs,array('callback_url'),' '.__FUNCTION__))
			{
				$req = parent::restRequest('oauth/request_token',$aArgs,true);
				$req->set_parameter('oauth_callback',$aArgs['callback_url']);
				$aResult = Immocaster_Tools_Helper::makeArrayFromString(parent::getContent($req));
				Immocaster_Data_Session::getInstance()->setVar('request_token',$aResult['oauth_token']);
				Immocaster_Data_Mysql::getInstance()->saveRequestToken($aResult['oauth_token'],$aResult['oauth_token_secret']);
				@header('Location: '.$this->_sUri.'/restapi/security/oauth/confirm_access?oauth_token='.$aResult['oauth_token']);
				echo '<meta http-equiv="refresh" content="0;url='.$this->_sUri.'/restapi/security/oauth/confirm_access?oauth_token='.$aResult['oauth_token'].'">';
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		return false;
	}
	
	/**
     * Applikation Accesstoken ermitteln
	 * und in Datenbank speichern (3-legged-oauth).
	 *
     * @param array $aArgs
     * @return void
     */
	private function registerAccess($aArgs)
	{
		try
		{
			if(Immocaster_Data_Mysql::getInstance()->getApplicationToken())
			{
				return false;
			}
			$oToken = Immocaster_Data_Mysql::getInstance()->getRequestToken(Immocaster_Data_Session::getInstance()->getVar('request_token'));
			$token = new OAuthToken
			(
				$oToken->ic_key,
				$oToken->ic_secret
			);
			$req = parent::restRequest('oauth/access_token',array(),true);
			$req->set_parameter('oauth_verifier',$_GET['oauth_verifier']);
			$req->set_parameter('oauth_token',$oToken->ic_key);
			$req->set_parameter('oauth_signature_method',"HMAC-SHA1");
			$req->sign_request($this->_oSignatureMethod,$this->_oConsumer,$token);
			$sConsKey = rawurlencode($this->_sConsumerSecret).'&'.$oToken->ic_secret;
			$sSignature = urlencode(base64_encode(hash_hmac('sha1',$req->get_signature_base_string(),$sConsKey,true)));
			$authHeader = $req->to_header();
			$opts = array('http'=>array('header'=>$authHeader.',oauth_signature_method="HMAC-SHA1",oauth_signature="'.
			$sSignature.'"'."\r\n".'User-Agent: '.IMMOCASTER_USER_AGENT));
			@$result = file_get_contents($this->_sUri.'/restapi/security/oauth/access_token',false,stream_context_create($opts));
			if(!$result)
			{
				 $this->registerRequest($aArgs);
				 return false;
			}
			$aAccessToken = Immocaster_Tools_Helper::makeArrayFromString($result);
			if(Immocaster_Data_Mysql::getInstance()->saveApplicationToken($aAccessToken['oauth_token'],$aAccessToken['oauth_token_secret']))
			{
				return true;
			}	
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		return false;
	}

	/**
	 * Application Accesstoken aus der Datenbank holen
	 * (3-legged-oauth)
	 *
	 * @return array (token, secret)
	 */
	private function getApplicationTokenAndSecret() {
		$oToken = NULL;
		$sSecret = NULL;
		if(class_exists('Immocaster_Data_Mysql') && $oData = Immocaster_Data_Mysql::getInstance()->getApplicationToken())
		{
			$oToken = new OAuthToken
			(
				$oData->ic_key,
				$oData->ic_secret
			);
			$sSecret = $oData->ic_secret;
		}
		return array($oToken, $sSecret);
	}
}