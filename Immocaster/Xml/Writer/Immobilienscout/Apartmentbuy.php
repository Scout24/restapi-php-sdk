<?php

/**
 * Immocaster SDK
 * Array mit Daten für den 
 * IS24-Export von Wohungen zum 
 * Kauf.
 * *********************************
 * Pflichtangaben:
 * title
 * zip
 * city
 * showFullAddress
 * baseBuyPrice
 * currency
 * livingSpaceSqm
 * numberOfRooms
 * *********************************
 * @package    Immocaster SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immocaster.com
 */

return array(
  'objectId'                       => array('xml' => array('externalId'), 'type'=>'string'),
  'title'                          => array('xml' => array('title'), 'type'=>'string'),
  'created'                        => array('xml' => array('creationDate'), 'type'=>'string'),
  'changed'                        => array('xml' => array('lastModificationDate'), 'type'=>'string'),
  'street'                         => array('xml' => array('address','street'), 'type'=>'string'),
  'houseNumber'                    => array('xml' => array('address','houseNumber'), 'type'=>'string'),
  'zip'                            => array('xml' => array('address','postcode'), 'type'=>'string'),
  'city'                           => array('xml' => array('address','city'), 'type'=>'string'),
  'longDescription'                => array('xml' => array('descriptionNote'), 'type'=>'string'),
  'furnishingDescription'          => array('xml' => array('furnishingNote'), 'type'=>'string'),
  'locationDescription'            => array('xml' => array('locationNote'), 'type'=>'string'),
  'otherDescription'               => array('xml' => array('otherNote'), 'type'=>'string'),
  'showFullAddress'                => array('xml' => array('showAddress'), 'type'=>'bool'),
  'floor'                          => array('xml' => array('floor'), 'type'=>'int'),
  'lift'                           => array('xml' => array('lift'), 'type'=>'bool-set'),
  'cellar'                         => array('xml' => array('cellar'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'handicappedAccessible'          => array('xml' => array('handicappedAccessible'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'totalFloors'                    => array('xml' => array('numberOfFloors'), 'type'=>'int'),
  'usableSpaceSqm'                 => array('xml' => array('usableFloorSpace'), 'type'=>'float'),
  'numberOfBedRooms'               => array('xml' => array('numberOfBedRooms'), 'type'=>'int'),
  'guestToilet'                    => array('xml' => array('guestToilet'), 'type'=>'bool-set', 'values'=>array('YES','NOT_APPLICABLE')),
  'baseBuyPrice'                   => array('xml' => array('price','value'), 'type'=>'float'),
  'currency'                       => array('xml' => array('price','currency'), 'type'=>'string'),
  'livingSpaceSqm'                 => array('xml' => array('livingSpace'), 'type'=>'float'),
  'numberOfRooms'                  => array('xml' => array('numberOfRooms'), 'type'=>'float'),
  'balcony'                        => array('xml' => array('balcony'), 'type'=>'bool-set'),
  'garden'                         => array('xml' => array('garden'), 'type'=>'bool-set'),
  'hasCourtage'                    => array('xml' => array('courtage','hasCourtage'), 'type'=>'bool-set', 'values'=>array('YES','NO')),
  'courtage'                       => array('xml' => array('courtage','courtage'), 'type'=>'string'),
  'courtageNote'                   => array('xml' => array('courtage','courtageNote'), 'type'=>'string')
);