<?php

/**
 * Immocaster SDK
 * Nutzung der ImmobilienScout24 API per REST.
 *
 * @package    Immocaster SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immocaster.com
 */

class Immocaster_Immobilienscout_Rest extends Immocaster_Immobilienscout
{
	
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
			$this->_sUri = 'http://rest.sandbox-immobilienscout24.de';
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
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		$req = $this->doRequest('search/v1.0/expose/'.$aArgs['exposeid'],$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('exposeid');
		return parent::getContent($req,$sSecret);
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
			$aArgs['username'] = 'me';
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['exposeid'],$aArgs,$aRequired,__FUNCTION__,$oToken);
		$req->unset_parameter('username');
		$req->unset_parameter('exposeid');
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
		if(isset($aArgs['username']))
		{
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
			if($oToken === NULL || $sSecret === NULL)
			{
				return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
			}
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
			$aArgs['username'] = 'me';
		}
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
			list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
		if(isset($aArgs['username']) && isset($aArgs['service']) && isset($aArgs['estate']))
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
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
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
		$aRequired = array('estateid');
		if(!isset($aArgs['username'])){ $aArgs['username'] = 'me'; }
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
		$aRequired = array('file','estateid');
		if(!isset($aArgs['username'])){ $aArgs['username'] = 'me'; }
		if(!isset($aArgs['title'])){ $aArgs['title'] = ''; }
		if(!isset($aArgs['floorplan'])){ $aArgs['floorplan'] = 'false'; }
		if(!isset($aArgs['titlePicture'])){ $aArgs['titlePicture'] = 'false'; }
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		if(!is_file($aArgs['file']))
		{
			return sprintf(IMMOCASTER_SDK_LANG_FILE_NOT_FOUND,$aArgs['file']);
		}
		$sMimeBoundary = md5(time());
		$aArgs['request_body'] = parent::createAttachmentBody($sMimeBoundary,$aArgs);
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
		$req->unset_parameter('type');
		return parent::getContent(
			$req,
			$sSecret,
			array(
				'Content-Type'=>'multipart/form-data; boundary="'.$sMimeBoundary.'"',
				'Accept-Encoding' => 'gzip,deflate'
			)
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
		$aRequired = array('attachmentid','estateid');
		if(!isset($aArgs['username'])){ $aArgs['username'] = 'me'; }
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
		if(isset($aArgs['username']) && isset($aArgs['service']) && isset($aArgs['estate']))
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
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/realestate/'.$aArgs['exposeid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'PUT');
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
		$aRequired = array('exposeid','channelid');
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
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/publish',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('exposeid');
		$req->unset_parameter('channelid');
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
		$aRequired = array('exposeid','channelid');
		$oToken = null;
		$sSecret = null;
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/publish/'.$aArgs['exposeid'].'_'.$aArgs['channelid'],$aArgs,$aRequired,__FUNCTION__,$oToken,'DELETE');
		$req->unset_parameter('exposeid');
		$req->unset_parameter('channelid');
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
			if($this->_sUrlReadingType == 'curl')
			{
				$ch = curl_init();
				curl_setopt($ch,CURLOPT_URL,$this->_sUri.'/restapi/security/oauth/access_token');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['http']);
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
	
	/**
	 * Kontakt eines Users über Contactid abfragen.
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
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
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
	 * Kontakt zu ImmobilienScout24 exportieren.
	 * (Hierfür müssen besondere Berechtigungen bei ImmobilienScout24 beantragt werden.
	 * Bitte informieren Sie sich direkt bei IS24 darüber.)
	 *
	 * @param array $aArgs
	 * @return mixed
	 */
	private function _exportContact($aArgs)
	{
		$aRequired = array('username','contact');
		if(isset($aArgs['username']) && isset($aArgs['contact']))
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
		list($oToken, $sSecret) = $this->getApplicationTokenAndSecret();
		if($oToken === NULL || $sSecret === NULL)
		{
			return IMMOCASTER_SDK_LANG_APPLICATION_NOT_CERTIFIED;
		}
		$req = $this->doRequest('offer/v1.0/user/'.$aArgs['username'].'/contact',$aArgs,$aRequired,__FUNCTION__,$oToken,'POST');
		$req->unset_parameter('username');
		$req->unset_parameter('contact');
		return parent::getContent($req,$sSecret);
	}
}
