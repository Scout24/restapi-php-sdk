<?php
session_start();
/**
 * ImmobilienScout24 PHP-SDK
 *  Nutzung der ImmobilienScout24 API per REST.
 *
 * @package    ImmobilienScout24 PHP-SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immobilienscout24.de
 */

class Immocaster_Immobilienscout_Rest extends Immocaster_Immobilienscout
{

	/**
     * Rückgabe des Request (False für normalen Request
	 * und true für Array mit Infos zum Request. Diese Option
	 * sollte nur von Entwicklern für Testzwecke genutzt werden.)
     *
     * @var boolean
     */
	 protected $_bRequestDebug = false;

	/**
	 * Leseprotokoll: standardmäßig cURL
	 * Die PHP-Funktion file_get_contents() wird nicht
	 * mehr unterstützt.
	 */
	 protected $_sUrlReadingType = 'curl';

	/**
	 * Ergebnisformat: JSON oder standardmäßig XML
	 */
	 protected $_sContentResultType = 'none';

	/**
	 * Anfrageformat: JSON oder standardmäßig XML
	 */
	 protected $_sContentRequestType = 'none';

	 /**
	 * Standard Nutzername für Abfragen per oAuth,
	 * wenn ein Nutzername für die Abfrage benötigt wird
	 */
	 protected $_sDefaultUsername = 'me';

	 /**
	 * Authentifizierung standardmäßig mit MySQL Datenbank durchführen
	 * false: MySQL, true: Session
	 */
	 protected $_bAuthenticateWithoutDB = false;

	 /**
	 * Requests standardmäßig mit http abgeschickt
	 */
	 protected $_sProtocol = 'http';

     /**
      * Proxy
      **/
     protected $_sProxyName = NULL;
     protected $_sProxyPort = NULL;

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
    public function setReadingType($sType='curl')
    {
		if(strtolower($sType)=='none')
		{
			$this->_sUrlReadingType = 'none';
			return true;
		}
		$this->_sUrlReadingType = 'curl';
		return true;
    }

	/**
     * Aktivieren des Debug-Mode für den Request
     *
     * @return boolean
     */
    public function enableRequestDebug()
    {
		$this->_bRequestDebug = true;
		return true;
    }

	/**
     * Deaktivieren des Debug-Mode für den Request
     *
     * @return boolean
     */
    public function disableRequestDebug()
    {
		$this->_bRequestDebug = false;
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
			$this->_sUri = $this->_sProtocol.'://rest.sandbox-immobilienscout24.de';
			return true;
		}
		if($sUrl=='live')
		{
			$this->_sUri = $this->_sProtocol.'://rest.immobilienscout24.de';
			return true;
		}
		$this->_sUri = $sUrl;
		return true;
	}

	/**
     * Strict-Mode aktivieren und deaktivieren.
	 *
	 * @param boolean $bMode False oder true für Strict-Mode.
	 * @return boolean
     */
	public function setStrictMode($bMode=false)
	{
		if($bMode===true || $bMode===false)
		{
			$this->_sStrictMode = $bMode;
			return true;
		}
		return false;
	}

    /**
     * Proxy-Einstellungen
     *
     * @param  string  $sProxyName  Name (oder IP) des Proxy-Servers
     * @param  string  $sProxyPort  optionaler Port (wird auf numeric geprueft)
     * @return void
     **/
    public function setProxy($sProxyName,$sProxyPort=NULL)
    {
        $this->_sProxyName = $sProxyName;
        if($sProxyPort&&is_numeric($sProxyPort))
        {
            $this->_sProxyPort = $sProxyPort;
        }
    }

	/**
      * Authentifizierung ohne MySQL Datenbank aktivieren und deaktivieren.
      *
      * @return boolean
      */
     public function authenticateWithoutDB($bAuthenticateWithoutDB)
     {
     	if ($bAuthenticateWithoutDB === true)
     	{
 			$this->_bAuthenticateWithoutDB = true;
 		}
 		else
 		{
 			$this->_bAuthenticateWithoutDB = false;
 		}
 		return true;
     }

    /**
     * https / http nutzen
     *
     * @return boolean
     */
     public function useHttps($bUseHttps)
     {
     	if ($bUseHttps === true)
     	{
 			$this->_sProtocol = 'https';
 		}
 		else
 		{
 			$this->_sProtocol = 'http';
 		}
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
			return $this->$sMethod($args[0]);
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
	 * @param boolean $postRequest TRUE, wenn der Request ein POST-Request sein soll
     * @return mixed
     */
	private function doRequest($sPath,$aArgs,$aRequired=array(),$sFunctionName,$oToken=null,$postRequest=FALSE)
	{
		$requestType = $postRequest ? 'POST' : 'GET';
		if($postRequest=='PUT'){$requestType='PUT';}
		if($postRequest=='DELETE'){$requestType='DELETE';}
		try
		{
			if(parent::requiredArgs($aArgs,$aRequired,$sFunctionName))
			{
				return parent::restRequest($sPath,$aArgs,false,$oToken,$requestType);
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
			unset($aArgs['country-id']);
			$iSearchOption = 1;
		}
		if(isset($aArgs['region-id']))
		{
			$sSearchQuery .= '/region/'.$aArgs['region-id'];
			unset($aArgs['region-id']);
			$iSearchOption++;
		}
		if(isset($aArgs['city-id']))
		{
			$sSearchQuery .= '/city/'.$aArgs['city-id'];
			unset($aArgs['city-id']);
			$iSearchOption++;
		}
		if(isset($aArgs['quarter-id']))
		{
			$sSearchQuery .= '/quarter/'.$aArgs['quarter-id'];
			unset($aArgs['quarter-id']);
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
     * Abfrage eines Exposes (Search-API)
	 * mit der Objekt-ID.
	 *
     * @param array $aArgs
     * @return mixed
     */
	private function _getExpose($aArgs)
	{
		$aRequired = array('exposeid');
		$req = $this->doRequest('search/v1.0/expose/'.$aArgs['exposeid'],$aArgs,$aRequired,__FUNCTION__);
		$req->unset_parameter('exposeid');
		return parent::getContent($req);
	}

	/**
     * Abfrage eines eigenen Exposes (Offer-API)
	 * mit der Objekt-ID.
	 *
     * @param array $aArgs
     * @return mixed
     */
	private function _getUserExpose($aArgs)
	{
		$aRequired = array('username','exposeid');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['exposeid'].'?usenewenergysourceenev2014values=true',$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('exposeid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
     * Impressum des Angebots anhand einer
	 * Objekt-ID auslesen.
	 *
     * @param array $aArgs
     * @return mixed
     */
	private function _getExposeImprint($aArgs)
	{
		$aRequired = array('exposeid');
		$req = $this->doRequest('search/v1.0/expose/'.$aArgs['exposeid'].'/imprint',$aArgs,$aRequired,__FUNCTION__);
		$req->unset_parameter('exposeid');
		return parent::getContent($req);
	}

	/**
	 * Logo des Anbieters auslesen.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getLogo($aArgs)
	{
		$aRequired = array('username');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest(
			'offer/v1.0/realtor/'.$aArgs['username'].'/logo',
			$aArgs,
			$aRequired,
			__FUNCTION__,
			$oToken
		);
		$req->unset_parameter('username');
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
			unset($aArgs['attachmentid']);
		}
		$req = $this->doRequest($sSearchQuery,$aArgs,$aRequired,__FUNCTION__);
		$req->unset_parameter('exposeid');
		return parent::getContent($req);
	}

	/**
     * Abfrage von Geo-Objekten anhand eines Inputs
     *
     * @param array $aArgs
     * @return mixed
     */
	private function _getGeoAutocompletionSuggestion($aArgs)
	{
		$aRequired = array('i','country');
		$req = $this->doRequest('gis/v2.0/geoautocomplete/'.$aArgs['country'],$aArgs,$aRequired,__FUNCTION__);
		return parent::getContent($req);
	}

	/**
     * Abfrage von Geo-Objekten anhand einer Geo-Entity-ID
     *
     * @param array $aArgs
     * @return mixed
     */
	private function _getGeoAutocompletionEntity($aArgs)
	{
		$aRequired = array('id','country');
		$req = $this->doRequest('gis/v2.0/geoautocomplete/'.$aArgs['country'].'/entity/'.$aArgs['id'],$aArgs,$aRequired,__FUNCTION__);
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
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
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
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
			if($oToken === NULL || $sSecret === NULL)
			{
				return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
			}
		}
		$req = $this->doRequest('search/v1.0/search/region',$aArgs,$aRequired,__FUNCTION__,$oToken);
		return parent::getContent($req,$sSecret);
	}

	/**
     * Abfrage der kompletten Ergebnisliste
	 * eines Kunden/Maklers/Börse.
     *
     * @param array $aArgs
     * @return mixed
     */
	private function _fullUserSearch($aArgs)
	{
		$aRequired = array('username');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate',$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Kontaktanfrage an den Anbieter eines Exposes (Objekt)
	 * mit der Objekt-ID.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _sendContact($aArgs)
	{
		$aRequired = array('exposeid', 'request_body');
		$oToken = null;
		$sSecret = null;
		if(isset($aArgs['username']))
		{
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
			if($oToken === NULL || $sSecret === NULL)
			{
				return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
			}
		}
		$req = $this->doRequest('search/v1.0/expose/'.$aArgs['exposeid'].'/contact',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('exposeid');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Kontakt eines Users über ContactId abfragen.
	 * (Hierfür müssen besondere Berechtigungen
	 * bei ImmobilienScout24 beantragt werden.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getContact($aArgs)
	{
		$aRequired = array('username','contactid');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/contact/'.$aArgs['contactid'],$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		$req->unset_parameter('contactid');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Kontaktinformation zu ImmobilienScout24 exportieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _exportContact($aArgs)
	{
		$aRequired = array('username','contact');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['contact']))
		{
			if(isset($aArgs['contact']['xml']))
			{
				$aArgs['request_body'] = $aArgs['contact']['xml'];
			}
			else
			{
				return sprintf(IMMOCASTER_SDK_LANG_XML_NOT_SET);
			}
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/contact',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('username');
		$req->unset_parameter('contact');
		return parent::getContent($req,$sSecret);
	}

	 /**
	 * Kontaktinformation ändern.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _changeContact($aArgs)
	{
		$aRequired = array('contactid','username','contact');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['contact']))
		{
			if(isset($aArgs['contact']['xml']))
			{
				$aArgs['request_body'] = $aArgs['contact']['xml'];
			}
			else
			{
				return sprintf(IMMOCASTER_SDK_LANG_XML_FORMAT_NOT_SET);
			}
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/contact/'.$aArgs['contactid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'PUT');
		$req->unset_parameter('contactid');
		$req->unset_parameter('username');
		$req->unset_parameter('contact');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * 'Send a friend' für eine Expose (Objekt)
	 * mit der Objekt-ID.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _sendAFriend($aArgs)
	{
		$aRequired = array('exposeid', 'request_body');
		$oToken = null;
		$sSecret = null;
		if(isset($aArgs['username']))
		{
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
			if($oToken === NULL || $sSecret === NULL)
			{
				return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
			}
		}
		$req = $this->doRequest('search/v1.0/expose/'.$aArgs['exposeid'].'/sendafriend',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('exposeid');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Channel ermitteln in dem ein User seine
	 * Objekte exportieren darf.
	 * (Hierfür müssen besondere Berechtigungen
	 * bei ImmobilienScout24 beantragt werden.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getPublishChannel($aArgs)
	{
		$aRequired = array('username');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/publishchannel',$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Objekt zu ImmobilienScout24 exportieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _exportObject($aArgs)
	{
		$aRequired = array('username','service','estate');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['service']) && isset($aArgs['estate']))
		{
			if(isset($aArgs['estate']['xml']))
			{
				$aArgs['request_body'] = $aArgs['estate']['xml'];
			}
			else
			{
				require_once(dirname(__FILE__).'/../Xml/Writer.php');
				$oXml = Immocaster_Xml_Writer::getInstance('xmlReqBody');
				if(!$oXml->setFormat(strtolower($aArgs['service']),array('estate_type'=>$aArgs['estate']['type'])))
				{
					return sprintf(IMMOCASTER_SDK_LANG_XML_FORMAT_NOT_SET,$aArgs['service']);
				}
				$aArgs['request_body'] = $oXml->getXml($aArgs['estate']);
			}
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate?usenewenergysourceenev2014values=true',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('username');
		$req->unset_parameter('service');
		$req->unset_parameter('estate');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Alle Anhänge zu einem Objekt per ExportAPI ermitteln.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getObjectAttachments($aArgs)
	{
		$aRequired = array('username','estateid');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest(
			'offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'].'/attachment',
			$aArgs,
			$aRequired,
			__FUNCTION__,
			$oToken
		);
		$req->unset_parameter('estateid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Anhang zu einem Objekt zu ImmobilienScout24 exportieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _exportObjectAttachment($aArgs)
	{
		$aRequired = array('username','estateid');

		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(!isset($aArgs['title'])){ $aArgs['title'] = ''; }
		if(!isset($aArgs['floorplan'])){ $aArgs['floorplan'] = 'false'; }
		if(!isset($aArgs['titlePicture'])){ $aArgs['titlePicture'] = 'false'; }
		if(!isset($aArgs['type'])){ $aArgs['type'] = ''; }
		if(!isset($aArgs['externalId'])){ $aArgs['externalId'] = ''; }
        if(!isset($aArgs['externalCheckSum'])){ $aArgs['externalCheckSum'] = ''; }
		$oToken = null;
		$sSecret = null;
		$aHeader = array();
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		if(($aArgs['type'] == 'Picture' or $aArgs['type'] == 'PDFDocument') && !is_file($aArgs['file']))		{
			return sprintf(IMMOCASTER_SDK_LANG_FILE_NOT_FOUND,$aArgs['file']);
		}
		$sMimeBoundary = md5(time());
		$aArgs['request_body'] = parent::createAttachmentBody($sMimeBoundary,$aArgs);
		if($aArgs['type'] == 'Picture' or $aArgs['type'] == 'PDFDocument' && $aArgs['type'] !== 'Link') {
		$aHeader = array(
                'Content-Type'=>'multipart/form-data; boundary="'.$sMimeBoundary.'"',
                'Accept-Encoding' => 'gzip,deflate'
            );
		}
		$req = $this->doRequest(
			'offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'].'/attachment',
			$aArgs,
			$aRequired,
			__FUNCTION__,
			$oToken,
			'POST'
		);
		$req->unset_parameter('title');
		$req->unset_parameter('floorplan');
		$req->unset_parameter('titlePicture');
		$req->unset_parameter('estateid');
		$req->unset_parameter('username');
		$req->unset_parameter('file');
		$req->unset_parameter('url');
		$req->unset_parameter('type');
		$req->unset_parameter('externalId');
        $req->unset_parameter('externalCheckSum');
		return parent::getContent(
			$req,
			$sSecret,
			$aHeader
		);
	}

	/**
	 * Anhang zu einem Objekt entfernen.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _deleteObjectAttachment($aArgs)
	{
		$aRequired = array('username','attachmentid','estateid');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest(
			'offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'].'/attachment/'.$aArgs['attachmentid'],
			$aArgs,
			$aRequired,
			__FUNCTION__,
			$oToken,
			'DELETE'
		);
		$req->unset_parameter('attachmentid');
		$req->unset_parameter('estateid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Objekt bei ImmobilienScout24 ändern.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _changeObject($aArgs)
	{
		$aRequired = array('exposeid','username','service','estate');
		if(!isset($aArgs['exposeid']) && isset($aArgs['estate']['objectId']))
		{
			$aArgs['exposeid'] = 'ext-'.$aArgs['estate']['objectId'];
		}
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['service']) && isset($aArgs['estate']))
		{
			if(isset($aArgs['estate']['xml']))
			{
				$aArgs['request_body'] = $aArgs['estate']['xml'];
			}
			else
			{
				require_once(dirname(__FILE__).'/../Xml/Writer.php');
				$oXml = Immocaster_Xml_Writer::getInstance('xmlReqBody');
				if(!$oXml->setFormat(strtolower($aArgs['service']),array('estate_type'=>$aArgs['estate']['type'])))
				{
					return sprintf(IMMOCASTER_SDK_LANG_XML_FORMAT_NOT_SET,$aArgs['service']);
				}
				$aArgs['request_body'] = $oXml->getXml($aArgs['estate']);
			}
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['exposeid'].'?usenewenergysourceenev2014values=true',$aArgs,$aRequired,__FUNCTION__,$oToken,'PUT');
		$req->unset_parameter('exposeid');
		$req->unset_parameter('username');
		$req->unset_parameter('service');
		$req->unset_parameter('estate');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Objekt bei ImmobilienScout24 aktivieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _enableObject($aArgs)
	{
		$aRequired = array('username','exposeid','channelid');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['xml']))
		{
			$aArgs['request_body'] = $aArgs['xml'];
		}
		else
		{
			$aArgs['request_body'] = '<common:publishObject xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:common="http://rest.immobilienscout24.de/schema/common/1.0">
        <realEstate id="'.$aArgs['exposeid'].'"/>
        <publishChannel id="'.$aArgs['channelid'].'"/>
</common:publishObject>';
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/publish',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('exposeid');
		$req->unset_parameter('channelid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Objekt bei ImmobilienScout24 deaktivieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _disableObject($aArgs)
	{
		$aRequired = array('username','exposeid','channelid');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/publish/'.$aArgs['exposeid'].'_'.$aArgs['channelid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('exposeid');
		$req->unset_parameter('channelid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Objekt bei ImmobilienScout24 löschen.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _deleteObject($aArgs)
	{
		$aRequired = array('username','estateid');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('estateid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

    /**
     * Kontakt bei ImmobilienScout24 löschen. Bestehende Immobilienzuordnungen werden durch den Standard Kontakt ersetzt.
     * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
     * Bitte informieren Sie sich direkt bei IS24 darüber.)
     *
     * @param array $aArgs
     * @return mixed
     */
    private function _deleteContact($aArgs)
	{
		$aRequired = array('username','contactid');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/contact/'.$aArgs['contactid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('contactid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * OnTop Platzierungen für mehrere Objekte bei ImmobilienScout24 buchen.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _postbylistOntopplacement($aArgs)
	{
		$aRequired = array('username','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['body']))
		{
			$aArgs['request_body'] = $aArgs['body'];
		}

		// create body for request
		else if(isset($aArgs['realestateids']))
		{
			// make array from realestateids string and count the array length
			$aRealestateids = explode ( ',' , $aArgs['realestateids']);
			$iArraysize = count($aRealestateids);

			$sBreak = "\r\n";
			$sBody = '<'.$aArgs['ontopplacementtype'].':'.$aArgs['ontopplacementtype'].'s xmlns:'.$aArgs['ontopplacementtype'].'="http://rest.immobilienscout24.de/schema/offer/'.$aArgs['ontopplacementtype'].'/1.0" xmlns:xlink="http://www.w3.org/1999/xlink">' . $sBreak;
			for ($i = 0; $i < $iArraysize; $i++) {
				$sBody .= '<'.$aArgs['ontopplacementtype'].' realestateid="'.$aRealestateids[$i].'"/>' . $sBreak;
			}
			$sBody .= '</'.$aArgs['ontopplacementtype'].':'.$aArgs['ontopplacementtype'].'s>';
			$aArgs['request_body'] = $sBody;
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/'.$aArgs['ontopplacementtype'].'/list',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('username');
		$req->unset_parameter('ontopplacementtype');
		$req->unset_parameter('body');
		$req->unset_parameter('realestateids');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Ein Objekt bei ImmobilienScout24 OnTop platzieren.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _postbyidOntopplacement($aArgs)
	{
		$aRequired = array('username','realestateid','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['realestateid'].'/'.$aArgs['ontopplacementtype'],$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('username');
		$req->unset_parameter('realestateid');
		$req->unset_parameter('ontopplacementtype');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * OnTop Platzierung eines Objektes bei ImmobilienScout24 auslesen.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getbyidOntopplacement($aArgs)
	{
		$aRequired = array('username','realestateid','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['realestateid'].'/'.$aArgs['ontopplacementtype'],$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		$req->unset_parameter('realestateid');
		$req->unset_parameter('ontopplacementtype');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Alle OnTop Platzierungen eines Accounts bei ImmobilienScout24 auslesen.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getallOntopplacement($aArgs)
	{
		$aRequired = array('username','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/'.$aArgs['ontopplacementtype'].'/all',$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		$req->unset_parameter('ontopplacementtype');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Alle OnTop Platzierungen eines Accounts bei ImmobilienScout24 löschen.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _deleteallOntopplacement($aArgs)
	{
		$aRequired = array('username','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/'.$aArgs['ontopplacementtype'].'/all',$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('username');
		$req->unset_parameter('ontopplacementtype');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * OnTop Platzierung eines Objektes bei ImmobilienScout24 löschen.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _deletebyidOntopplacement($aArgs)
	{
		$aRequired = array('username','realestateid','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['realestateid'].'/'.$aArgs['ontopplacementtype'],$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('username');
		$req->unset_parameter('realestateid');
		$req->unset_parameter('ontopplacementtype');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Liste von OnTop Platzierung bei ImmobilienScout24 löschen.
	 * Möglich sind folgende OnTop Platzierungen: Top, Premium und Schaufenster.
	 * OnTop Platzierungen müssen extra gebucht werden.
	 * ontopplacementtype: topplacement, premiumplacement, showcaseplacement.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _deletebylistOntopplacement($aArgs)
	{
		$aRequired = array('username','realestateids','ontopplacementtype');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/'.$aArgs['ontopplacementtype'].'/list?realestateids='.$aArgs['realestateids'],$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('username');
		$req->unset_parameter('realestateids');
		$req->unset_parameter('ontopplacementtype');
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
			$aAccessToken = $this->registerAccess($aArgs);
			if (is_array($aAccessToken)) {
				return $aAccessToken;
			}
			elseif ($aAccessToken === false) return false;
			else return true;
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
				// Wenn mit SQL Datenbank authentifiziert werden soll, speichere Reqeust Token und Secret in DB
				if ($this->_bAuthenticateWithoutDB === false) {
					Immocaster_Data_Mysql::getInstance()->saveRequestToken($aResult['oauth_token'],$aResult['oauth_token_secret']);
				}
				// andernfalls speichere Request Token und Secret in Session
				else {
					$_SESSION['request_token'] = $aResult['oauth_token'];
					$_SESSION['request_token_secret'] = $aResult['oauth_token_secret'];
				}
				@header('Location: '.$this->_sUri.'/restapi/security/oauth/confirm_access?oauth_token='.$aResult['oauth_token']);
				echo '<meta http-equiv="refresh" content="0;url='.$this->_sUri.
				'/restapi/security/oauth/confirm_access?oauth_token='.$aResult['oauth_token'].'">';
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
			if(isset($_GET['user']) && $_GET['user'] != ''){ $sUser=$_GET['user']; }else{ $sUser=$this->_sDefaultUsername; }
			// Wenn mit SQL Datenbank authentifiziert werden soll, hole Reqeust Token und Secret aus DB
			if ($this->_bAuthenticateWithoutDB === false)
			{
				if(Immocaster_Data_Mysql::getInstance()->getApplicationToken($sUser))
				{
					return false;
				}
				$oToken = Immocaster_Data_Mysql::getInstance()->getRequestTokenWithoutSession();
				$sToken = $oToken->ic_key;
				$sSecret = $oToken->ic_secret;
			}
			// andernfalls hole Request Token und Secret aus Session
			else {
				$sToken = $_SESSION['request_token'];
				$sSecret = $_SESSION['request_token_secret'];
				unset($_SESSION);
			}
			$token = new OAuthToken
				(
					$sToken,
					$sSecret
				);
			$req = parent::restRequest('oauth/access_token',array(),true);
			$req->set_parameter('oauth_verifier',$_GET['oauth_verifier']);
			$req->set_parameter('oauth_token',$sToken);
			$req->set_parameter('oauth_signature_method',"HMAC-SHA1");
			$req->sign_request($this->_oSignatureMethod,$this->_oConsumer,$token);
			$sConsKey = rawurlencode($this->_sConsumerSecret).'&'.$sSecret;
			$sSignature = urlencode(base64_encode(hash_hmac('sha1',$req->get_signature_base_string(),$sConsKey,true)));
			$authHeader = $req->to_header();
			$opts = array('http'=>array('header'=>$authHeader.',oauth_signature_method="HMAC-SHA1",oauth_signature="'.
			$sSignature.'"'."\r\n".'User-Agent: '.IMMOCASTER_USER_AGENT));
			if($this->_sUrlReadingType == 'curl')
			{
				$ch = curl_init();
				curl_setopt($ch,CURLOPT_URL,$this->_sUri.'/restapi/security/oauth/access_token');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['http']);
                if($this->_sProxyName)
                {
                    curl_setopt($ch, CURLOPT_PROXY, $this->_sProxyName);
                    if($this->_sProxyPort)
                    {
                        curl_setopt($ch, CURLOPT_PROXYPORT, $this->_sProxyPort);
                    }
                }
				$result = curl_exec($ch);
				curl_close($ch);
			}else{
				$result = file_get_contents($this->_sUri.'/restapi/security/oauth/access_token',false,stream_context_create($opts));
			}
			if(!$result)
			{
				 $this->registerRequest($aArgs);
				 return false;
			}
			$aAccessToken = Immocaster_Tools_Helper::makeArrayFromString($result);
			if ($this->_bAuthenticateWithoutDB === false) {
				if(Immocaster_Data_Mysql::getInstance()->saveApplicationToken(
					$aAccessToken['oauth_token'],
					$aAccessToken['oauth_token_secret'],
					$sUser
				))
				{
					return true;
				}
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		return $aAccessToken;
	}

	/**
	 * Application Accesstoken aus der Datenbank holen
	 * (3-legged-oauth)
	 *
	 * @return mixed
	 */
	private function getApplicationTokenAndSecret($sUser=self::_sDefaultUsername)
	{
		$oToken = NULL;
		$sSecret = NULL;
		if(class_exists('Immocaster_Data_Mysql') && $oData = Immocaster_Data_Mysql::getInstance()->getApplicationToken($sUser))
		{
			if($oData->ic_key!='')
			{
				$oToken = new OAuthToken
				(
					$oData->ic_key,
					$oData->ic_secret
				);
				$sSecret = $oData->ic_secret;
			}
			else
			{
				return null;
			}
		}
		return array($oToken, $sSecret);
	}

	/**
	 * Alle zertifizierten Benutzernamen auslesen
	 *
	 * @return array
	 */
	public function _getAllApplicationUsers($aArgs)
	{
		$aUsers = array();
		if(class_exists('Immocaster_Data_Mysql'))
		{
			$aUsers = Immocaster_Data_Mysql::getInstance()->getAllApplicationUsers();
			// Rückgabe als String (kommagetrennt)
			if(isset($aArgs['string']))
			{
				$sReturn = '';
				$iUserAmount = count($aUsers);
				$iCountUser = 1;
				foreach($aUsers as $sUser)
				{
					$sReturn .= $sUser;
					if($iCountUser<$iUserAmount){ $sReturn .= ', '; $iCountUser++; }
				}
				return $sReturn;
			}
		}
		// Rückgabe als Array
		return $aUsers;
	}

	/**
	 *
	 *  Video Upload Ticket von IS24 erhalten
	 *  Man erhält in Response unter anderem
	 *  videoId und auth Wert
	 *
	 */
	private function _getVideoUploadTicket($aArgs)
	{
		$aRequired = array('username');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/videouploadticket',$aArgs,$aRequired,__FUNCTION__,$oToken);

		return parent::getContent($req,$sSecret);
	}

	/**
     *
     * Video bei picsearch hochladen
     * 2 Anhänge: auth und das Video an sich
     *
     */
    private function _postVideoToPicsearch($aParameter)
    {
        $postValues = array
            (
                'auth' => $aParameter['auth'],
                'videofile' => '@'.$aParameter['file']
            );

        $ch = curl_init($aParameter['uploadUrl']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postValues);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if($this->_sProxyName)
		{
    		curl_setopt($ch, CURLOPT_PROXY, $this->_sProxyName);
    		if($this->_sProxyPort)
    		{
        		curl_setopt($ch, CURLOPT_PROXYPORT, $this->_sProxyPort);
    		}
		}

        try
        {
            $httpDescription = curl_exec($ch);
            $httpResponseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
        catch (Exception $pException)
        {
            // exception handling
        }

        if ('200' == $httpResponseStatusCode)
        {
            return $httpResponseStatusCode;
        }

        return $httpResponseStatusCode;
    }

	/**
	 * StreamingVideo zu einem Objekt zu ImmobilienScout24 exportieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _exportObjectVideoAttachment($aArgs)
	{
		$aRequired = array('username','estateid','videoid');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(!isset($aArgs['title'])){ $aArgs['title'] = ''; }
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$sBreak = "\r\n";
		$sBody 	= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $sBreak;
		$sBody  = '<common:attachment xsi:type="common:StreamingVideo" xmlns:common="http://rest.immobilienscout24.de/schema/common/1.0"
xmlns:ns3="http://rest.immobilienscout24.de/schema/platform/gis/1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $sBreak;
		$sBody .= '<title>'.$aArgs['title'].'</title>' . $sBreak;
		$sBody .= '<videoId>'.$aArgs['videoid'].'</videoId>' . $sBreak;
		$sBody .= '</common:attachment>';
		$aArgs['request_body'] = $sBody;
		$req = $this->doRequest(
			'offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'].'/attachment',
			$aArgs,
			$aRequired,
			__FUNCTION__,
			$oToken,
			'POST'
		);
		$req->unset_parameter('username');
		$req->unset_parameter('title');
		$req->unset_parameter('estateid');
		$req->unset_parameter('videoid');
		return parent::getContent(
			$req,
			$sSecret
		);
	}

	/**
	 * Attachmentsreihenfolge ändern.
	 * Als Input kann ein Array von ESTATEIDs dienen oder ein XML.
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _changeObjectAttachmentsorder($aArgs)
	{
		$aRequired = array('username','estateid');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		if(isset($aArgs['body']))
		{
			$aArgs['request_body'] = $aArgs['body'];
		}
		// create body for request
		else if(isset($aArgs['attachmentids']))
		{
			// make array from attachmentids string and count the array length
			$aAttachmentids = explode ( ',' , $aArgs['attachmentids']);
			$iArraysize = count($aAttachmentids);
			$sBreak = "\r\n";
			$sBody = '<attachmentsorder:attachmentsorder xmlns:attachmentsorder="http://rest.immobilienscout24.de/schema/attachmentsorder/1.0">' . $sBreak;
			for ($i = 0; $i < $iArraysize; $i++) {
				$sBody .= '<attachmentId>'.$aAttachmentids[$i]. '</attachmentId>' . $sBreak;
			}
			$sBody .= '</attachmentsorder:attachmentsorder>';
			$aArgs['request_body'] = $sBody;
		}
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'].'/attachment/attachmentsorder',$aArgs,$aRequired,__FUNCTION__,$oToken,'PUT');
		$req->unset_parameter('estateid');
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

	/**
	 * Attachment(Meta)daten ändern
	 * Nur Infos über Bild und nicht binären Daten
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _changeObjectAttachment($aArgs)
	{
		$aRequired = array('username','estateid','attachmentid','type');
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		// create body for different attachment types
		$sBreak = "\r\n";
		$sBody = '<common:attachment xsi:type="common:' .$aArgs['type']. '" xmlns:common="http://rest.immobilienscout24.de/schema/common/1.0" xmlns:ns3="http://rest.immobilienscout24.de/schema/platform/gis/1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $sBreak;
		$sBody .= '<title>' .$aArgs['title']. '</title>' . $sBreak;
		$sBody .= '<externalId>' .$aArgs['externalId']. '</externalId>' . $sBreak;
		if($aArgs['type'] == 'Picture' or $aArgs['type'] == 'PDFDocument') {
			$sBody .= '<floorplan>' .$aArgs['floorplan']. '</floorplan>' . $sBreak;
		}
		if($aArgs['type'] == 'Picture') {
			$sBody .= '<titlePicture>' .$aArgs['titlePicture']. '</titlePicture>' . $sBreak;
		}
		if($aArgs['type'] == 'Link') {
			$sBody .= '<url>' .$aArgs['url']. '</url>' . $sBreak;
		}
		$sBody .= '</common:attachment>';
		$aArgs['request_body'] = $sBody;

		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['estateid'].'/attachment/'.$aArgs['attachmentid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'PUT');
		$req->unset_parameter('estateid');
		$req->unset_parameter('username');
		$req->unset_parameter('attachmentid');
		$req->unset_parameter('type');
		return parent::getContent($req,$sSecret);
	}
	/**
	 * Publishchannels eines Objektes ermitteln
	 * (Hierfür müssen besondere Berechtigungen
	 * bei ImmobilienScout24 beantragt werden.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _getPublish($aArgs)
	{
		$aRequired = array('username','realestate');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/publish',$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		return parent::getContent($req,$sSecret);
	}

    /**
     * Füge Objekt einem Projekt hinzu
     *
     * @author chris <chris@musicchris.de>
     *
     * @param array $aArgs
     * @return mixed
     */
    public function addToProject($aArgs)
    {
        $aRequired = array('username', 'estateid', 'project_id');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$aArgs['request_body'] = '<realestateproject:realEstateProjectEntries xmlns:realestateproject="http://rest.immobilienscout24.de/schema/offer/realestateproject/1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ns4="http://rest.immobilienscout24.de/schema/platform/gis/1.0">
   <realEstateProjectEntry>
      <realEstateExternalId>' . $aArgs["estateid"] . '</realEstateExternalId>
   </realEstateProjectEntry>
</realestateproject:realEstateProjectEntries>';

		$req = $this->doRequest(
            'offer/v1.0/user/'.$aArgs['username'].'/realestateproject/'.$aArgs["project_id"].'/realestateprojectentry',
            $aArgs,
            $aRequired,
            __FUNCTION__,
            $oToken,
            'POST'
        );

        return parent::getContent($req,$sSecret);
    }

    /**
     * Entferne Objekt aus einem Projekt
     *
     * @author chris <chris@musicchris.de>
     *
     * @param array $aArgs
     * @return mixed
     */
    public function deleteFromProject($aArgs)
    {
        $aRequired = array('username', 'estateid', 'project_id');
		$oToken = null;
		$sSecret = null;
		if(!isset($aArgs['username']))
		{
			$aArgs['username'] = $this->_sDefaultUsername;
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret($aArgs['username']);
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest(
            'offer/v1.0/user/'.$aArgs['username'].'/realestateproject/'.$aArgs["project_id"].'/realestateprojectentry/ext-'.$aArgs["estateid"],
            $aArgs,
            $aRequired,
            __FUNCTION__,
            $oToken,
            'DELETE'
        );

        return parent::getContent($req,$sSecret);
    }
}
