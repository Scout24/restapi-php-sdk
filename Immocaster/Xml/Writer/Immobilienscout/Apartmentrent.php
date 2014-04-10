<?php

/**
 * ImmobilienScout24 PHP-SDK
 * Array mit Daten für den 
 * IS24-Export von Mietwohnungen.
 * *********************************
 * Pflichtangaben:
 * title
 * zip
 * city
 * showFullAddress
 * baseRentPrice
 * currency
 * livingSpaceSqm
 * numberOfRooms
 * *********************************
 * @package    ImmobilienScout24 PHP-SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immobilienscout24.de
 */

return array(
  'objectId'                       => array('xml' => array('externalId'), 'type'=>'string'),
  'title'                          => array('xml' => array('title'), 'type'=>'string'),
  'created'                        => array('xml' => array('creationDate'), 'type'=>'date'),
  'changed'                        => array('xml' => array('lastModificationDate'), 'type'=>'date'),
  'street'                         => array('xml' => array('address','street'), 'type'=>'string'),
  'houseNumber'                    => array('xml' => array('address','houseNumber'), 'type'=>'string'),
  'zip'                            => array('xml' => array('address','postcode'), 'type'=>'string'),
  'city'                           => array('xml' => array('address','city'), 'type'=>'string'),
  'latitude'                       => array('xml' => array('address','wgs84Coordinate','latitude'), 'type'=>'double'),
  'longitude'                      => array('xml' => array('address','wgs84Coordinate','longitude'), 'type'=>'double'),
  'longDescription'                => array('xml' => array('descriptionNote'), 'type'=>'string'),
  'furnishingDescription'          => array('xml' => array('furnishingNote'), 'type'=>'string'),
  'locationDescription'            => array('xml' => array('locationNote'), 'type'=>'string'),
  'otherDescription'               => array('xml' => array('otherNote'), 'type'=>'string'),
  'showFullAddress'                => array('xml' => array('showAddress'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'floor'                          => array('xml' => array('floor'), 'type'=>'int'),
  'lift'                           => array('xml' => array('lift'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'cellar'                         => array('xml' => array('cellar'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'handicappedAccessible'          => array('xml' => array('handicappedAccessible'), 'type'=>'bool-set'),
  'lastRefurbishment'              => array('xml' => array('lastRefurbishment'), 'type'=>'string'),
  'interiorQuality'                => array('xml' => array('interiorQuality'), 'type'=>'string-set', 'values'=>array('simple'=>'SIMPLE','normal'=>'NORMAL','luxury'=>'LUXURY','sophisticated'=>'SOPHISTICATED','default'=>'NO_INFORMATION')),
  'constructionYear'               => array('xml' => array('constructionYear'), 'type'=>'string'),
  'freeFrom'                       => array('xml' => array('freeFrom'), 'type'=>'string'),
  'heatingType'                    => array('xml' => array('heatingType'), 'type'=>'string-set', 'values'=>array('floor'=>'SELF_CONTAINED_CENTRAL_HEATING','central'=>'CENTRAL_HEATING','stove'=>'STOVE_HEATING','default'=>'NO_INFORMATION')),
  'buildingEnergyRatingType'       => array('xml' => array('buildingEnergyRatingType'), 'type'=>'string-set', 'values'=>array('required'=>'ENERGY_REQUIRED','consumption'=>'ENERGY_CONSUMPTION','default'=>'NO_INFORMATION')),
  'thermalCharacteristic'          => array('xml' => array('thermalCharacteristic'), 'type'=>'double'),
  'energyContainsWater'            => array('xml' => array('energyConsumptionContainsWarmWater'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'totalFloors'                    => array('xml' => array('numberOfFloors'), 'type'=>'int'),
  'usableSpaceSqm'                 => array('xml' => array('usableFloorSpace'), 'type'=>'double'),
  'numberOfBedRooms'               => array('xml' => array('numberOfBedRooms'), 'type'=>'double'),
  'guestToilet'                    => array('xml' => array('guestToilet'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'baseRentPrice'                  => array('xml' => array('baseRent'), 'type'=>'double'),
  'totalRentPrice'                 => array('xml' => array('totalRent'), 'type'=>'double'),
  'serviceCharge'                  => array('xml' => array('serviceCharge'), 'type'=>'double'),   
  'petsAllowed'                    => array('xml' => array('petsAllowed'), 'type'=>'bool-set', 'values'=>array('YES','NO')),
  'buyPriceCurrency'               => array('xml' => array('price','currency'), 'type'=>'string-set', 'values'=>array('default'=>'EUR')),
  'livingSpaceSqm'                 => array('xml' => array('livingSpace'), 'type'=>'double'),
  'numberOfRooms'                  => array('xml' => array('numberOfRooms'), 'type'=>'double'),
  'balcony'                        => array('xml' => array('balcony'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'garden'                         => array('xml' => array('garden'), 'type'=>'bool'),
  'hasCourtage'                    => array('xml' => array('courtage','hasCourtage'), 'type'=>'bool'),
  'courtage'                       => array('xml' => array('courtage','courtage'), 'type'=>'string'),
  'courtageNote'                   => array('xml' => array('courtage','courtageNote'), 'type'=>'string')
);
