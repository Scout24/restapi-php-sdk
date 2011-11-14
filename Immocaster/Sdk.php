<?php

/**
 * Immocaster SDK
 * Mit dem Immocaster SDK können Daten (Immobilien, Geo-Daten, usw.) über
 * die Schnittstelle (API) von ImmobilienScout24 ausgelesen werden.
 *
 * @package    Immocaster SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immocaster.com
 * @version    1.1.18
 */

class Immocaster_Sdk
{
	
    /**
     * Version and Application
	 *
     * @var string
     */
	 private $_sImmocasterApplication = 'SDK';
	 private $_sImmocasterVersion = '1_1_x';	

    /**
     * Service Objekt
	 *
     * @var mixed
     */
	 private $_oService = false;
	 
    /**
     * Singleton Instanzen
     *
     * @var array
     */
	static private $_instances = array();
	
    /**
     * Singleton Pattern für die Erstellung
	 * der Instanzen von Immocaster_Sdk.
     *
	 * @param string Name der Instanz
	 * @param string Key des Konsumenten
	 * @param string Secret des Konsumenten
	 * @param string Name des Service
	 * @param string Typ der Authentifizierung
	 * @param string Typ des Protokolls
     * @return Immocaster_Sdk
     */
	static public function getInstance($sName,$sKey='',$sSecret='',$sService='immobilienscout',$sAuth='oauth',$sProtocol='rest')
	{
		if(!isset(self::$_instances[$sName]))
		{
			self::$_instances[$sName] = new self($sKey,$sSecret,$sService,$sAuth,$sProtocol);
		}
		return self::$_instances[$sName];
	}
	
    /**
     * Abhängige Dateien laden und Verbindung
	 * zu einem Service aufbauen.
     *
	 * @param string Key des Konsumenten
	 * @param string Secret des Konsumenten
	 * @param string Name des Service
	 * @param string Typ der Authentifizierung
	 * @param string Typ des Protokolls
     * @return boolean
     */
	protected function __construct($sKey,$sSecret,$sService,$sAuth,$sProtocol)
	{
		if(!defined('IMMOCASTER_USER_AGENT'))
		{
			define('IMMOCASTER_USER_AGENT','Immocaster '.$this->_sImmocasterApplication.' v'.$this->_sImmocasterVersion);
		}
		require_once(dirname(__FILE__).'/Language/de_de.php');
		require_once(dirname(__FILE__).'/Tools/Helper.php');
		if(strtolower($sAuth)=='oauth')
		{
			require_once(dirname(__FILE__).'/Oauth/OAuth.php');
		}
		if(strtolower($sService)=='immobilienscout')
		{
			require_once(dirname(__FILE__).'/Immobilienscout/Immobilienscout.php');
			if(strtolower($sProtocol)=='rest')
			{
				require_once(dirname(__FILE__).'/Immobilienscout/Rest.php');
				$this->_oService = new Immocaster_Immobilienscout_Rest($sKey,$sSecret,$sAuth);
				return true;
			}
		}
		echo sprintf(IMMOCASTER_SDK_LANG_CANNOT_CONNECT_SERVICE,$sService,$sAuth.'/'.$sProtocol);
		return false;
	}
	
    /**
     * Aufgerufene Methode in den jeweiligen
	 * Service weiterleiten und dort aufrufen.
     *
	 * @param string
	 * @param array
     * @return mixed
     */
	public function __call($method,$args)
	{
		if($this->_oService)
		{
			return $this->_oService->$method($args[0]);
		}
		echo IMMOCASTER_SDK_LANG_NO_SERVICE_FOUND;
		return false;
	}
	
	/**
     * Haupt-URL zum Service ändern.
	 *
	 * @param string
	 * @return mixed
     */
	public function setRequestUrl($sUrl=false)
	{
		if($this->_oService)
		{
			return $this->_oService->setRequestUrl($sUrl);
		}
		echo IMMOCASTER_SDK_LANG_NO_SERVICE_FOR_CHANGE_URL;
		return false;
	}
	
	/**
     * Datenspeicherung per Session und Datenbank
	 * initialisieren (für 3-legged-oauth).
	 *
	 * @var array Parameters für die Datenbank (type,host,user,password,database)
	 * @var string Namespace für Variablen innerhalb der Session
	 * @var string Alternativer Name für den Tabellennamen
	 * @return boolean
     */
	public function setDataStorage($aConnection,$sSessionNamespace=null,$sTableName=null)
	{
		require_once('Data/Session.php');
		Immocaster_Data_Session::getInstance($sSessionNamespace);
		$sFileName = ucfirst(strtolower($aConnection[0]));
		require_once('Data/'.$sFileName.'.php');
		$this->oDataStorage = call_user_func(array('Immocaster_Data_'.$sFileName,'getInstance'),$aConnection,$sTableName);
		return $this->oDataStorage;
	}
	
}