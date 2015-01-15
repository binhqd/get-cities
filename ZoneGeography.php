<?php
require_once ('./libs/Guzzle/vendor/autoload.php');
require_once ('./libs/IDHelper.php');

/**
 * ZoneGeography
 *
 * @category Zone
 * @package ZoneGeography
 * @author Mr. Nav <mr.nav90@gmail.com>
 * @license YouLook Inc
 * @version Release: 2.0
 * @link http://youlook.net
 */
class ZoneGeography
{

    protected $country;

    protected $zoneId;

    protected $geoURL = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

    protected $geographyData = array();

    private $administrativeLevel1;

    private $administrativeLevel2;

    private $administrativeLevel3;

    private $administrativeLevel4;

    private $administrativeLevel5;

    private $locality;

    private $sublocality;

    private $route;

    private $premise;

    private $airport;

    private $park;

    private $point_of_interest;

    private $establishment;

    /**
     * This method is used get data from google api
     *
     * @param string $address
     *            address query data
     *            @throw if data is empty
     *            
     * @author vietna <vietna@greenglobal.com>
     * @return void
     */
    private function _getDataFromAddress($address)
    {
        $data = (new \GuzzleHttp\Client())->get($this->geoURL . $address)->json();
        if (! empty($data['results'])) {
            $this->geographyData = $data['results'][0]['address_components'];
        } else {
            throw new Exception('Data is empty', 500);
        }
    }

    /**
     * This method is used save data zone geography
     *
     * @param string $address
     *            address query data
     * @param boolean $isReturn
     *            return data after save
     *            @throw if data is empty
     *            
     * @author vietna <vietna@greenglobal.com>
     * @return array
     */
    public function query($address, $isReturn = false)
    {
        $dataReturn = array();
        $identifierKey = array();
        $identifierValue = array();
        $this->_getDataFromAddress($address);
        $zones = $this->_prepareData();
        $identifier = $zones['identifier'];
        $political = $zones['political'];
        $route = $zones['-route-'];
        if (! empty($identifier)) {
            $identifierKey = array_keys($identifier);
            $identifierValue = current($identifier);
            $zones = array_merge($zones, array(
                current($identifierKey) => current($identifier)
            ));
            unset($identifierKey[0]);
        }
        unset($zones['identifier'], $zones['political']);
        $zoneIds = array();
        foreach ($zones as $key => $value) {
            if (isset($value['name']) && ! empty($value['name'])) {
                $zone = new ZoneGeography();
                $zoneName = $this->_clearZoneGeoraphy($value['name']);
                // $location = $this->_getNodeLocation($value['name']);
                $zoneID = IDHelper::uuid(false);
                $zoneAttributes = array(
                    'name' => $zoneName,
                    'timestamp' => time(),
                    // 'lat' => $location['lat'],
                    // 'lon' => $location['lng'],
                    'weight' => 1,
                    'score' => 1,
                    'value' => $value['type']
                );
                $dataReturn[$value['type']] = $zoneAttributes;
            }
        }
        
        if ($isReturn) {
            return $dataReturn;
        }
    }

    /**
     * This method is used prepare data geography
     *
     * @throw if data is empty
     *
     * @author vietna <vietna@greenglobal.com>
     * @return array
     */
    private function _prepareData()
    {
        if (empty($this->geographyData)) {
            throw new Exception('Data is empty', 500);
        }
        $parseData = $this->_parseData($this->geographyData);
        $this->country = $parseData['country']['long_name'];
        $groupKey = $this->_getGroupByCountry();
        return $this->_getCountryStruct($groupKey, $parseData);
    }

    /**
     * This method is used get location of node
     *
     * @param string $address
     *            address for search location
     *            
     * @author vietna <vietna@greenglobal.com>
     * @return array
     */
    private function _getNodeLocation($address)
    {
        $response = (new \GuzzleHttp\Client())->get($this->geoURL . $address)->json();
        return $response['results'][0]['geometry']['location'];
    }

    /**
     * This method is used parse data geography
     *
     * @param array $data
     *            data country
     *            @throw if data is empty
     *            
     * @author vietna <vietna@greenglobal.com>
     * @return array
     */
    private function _parseData(array $data = array())
    {
        if (! empty($data)) {
            $result = array();
            for ($i = count($data) - 1; $i >= 0; $i --) {
                $types = $data[$i]['types'];
                $typeIdentifiers = current($data);
                if (in_array('route', $types)) {
                    $result['route']['name'] = $data[$i]['long_name'];
                    $result['route']['label'] = 'route';
                }
                if (end($types) == 'political') {
                    $result['political'][] = $data[$i]['long_name'];
                } else 
                    if ($typeIdentifiers && ! empty($typeIdentifiers['types'])) {
                        foreach ($typeIdentifiers['types'] as $key => $value) {
                            if (in_array($value, array(
                                'premise',
                                'airport',
                                'park',
                                'point_of_interest',
                                'establishment'
                            ))) {
                                $keyIdentifier = $typeIdentifiers['types'][$key];
                                $result['identifier']["-$keyIdentifier-"] = array(
                                    'name' => $data[$i]['long_name'],
                                    'label' => $typeIdentifiers['types'][$key]
                                );
                            }
                        }
                    }
                if (in_array('country', $types)) {
                    $result['country']['long_name'] = $data[$i]['long_name'];
                    $result['country']['short_name'] = $data[$i]['short_name'];
                } else 
                    if (in_array($types[0], $this->_getAdminLevels())) {
                        $result[$types[0]]['long_name'] = $data[$i]['long_name'];
                        $result[$types[0]]['short_name'] = $data[$i]['short_name'];
                    } else 
                        if (in_array('locality', $types)) {
                            $result['locality']['long_name'] = $data[$i]['long_name'];
                            $result['locality']['short_name'] = $data[$i]['short_name'];
                        } else 
                            if (in_array('sublocality', $types)) {
                                $result['sublocality']['long_name'] = $data[$i]['long_name'];
                                $result['sublocality']['short_name'] = $data[$i]['short_name'];
                            }
            }
            return $result;
        } else {
            throw new Exception('Parse data error', 500);
        }
    }

    /**
     * This method is used get administrative levels
     *
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return array
     */
    private function _getAdminLevels()
    {
        return array(
            'administrative_area_level_1',
            'administrative_area_level_2',
            'administrative_area_level_3',
            'administrative_area_level_4',
            'administrative_area_level_5'
        );
    }

    /**
     * This method is used get list group countries
     *
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return array
     */
    private function _getGroups()
    {
        return array(
            'group1' => array(
                'Vietnam'
            ),
            'group2' => array(
                'Spain'
            ),
            'group3' => array(
                'Liberia',
                'Lithuania'
            ),
            'group4' => array(
                'Japan',
                'Taiwan'
            ),
            'group5' => array(
                'Hungary',
                'Norway',
                'Romania',
                'Sweden'
            ),
            'group6' => array(
                'United Kingdom'
            ),
            'group7' => array(
                'Iran'
            ),
            'group8' => array(
                'Argentina'
            ),
            'group9' => array(
                'South Korea'
            ),
            'group10' => array(
                'Canada'
            ),
            'group11' => array(
                'Ireland',
                'Poland'
            ),
            'group12' => array(
                'New Zealand'
            ),
            'group13' => array(
                'China'
            ),
            'group14' => array(
                'France'
            ),
            'group15' => array(
                'Italy'
            ),
            'group16' => array(
                'Denmark'
            ),
            'group17' => array(
                'United States'
            ),
            'group18' => array(
                'Mexico',
                'Australia'
            ),
            'group19' => array(
                'Germany'
            )
        );
    }

    /**
     * This method is used clear name node georaphy
     *
     * @param string $name
     *            name of node
     *            
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return void
     */
    private function _clearZoneGeoraphy($name)
    {
        $filters = array(
            ' County',
            ' Province',
            ' Community',
            ' Region',
            ' District',
            ' City',
            ' Department',
            ' Municipality'
        );
        return str_replace($filters, '', $name);
    }

    /**
     * This method is used get group by country
     *
     * @throw if country or group not found
     *
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return array
     */
    private function _getGroupByCountry()
    {
        if (!empty($this->country)) {
            foreach ($this->_getGroups() as $key => $value) {
                if (in_array($this->country, $value)) {
                    return $key;
                }
            }
            return 'default';
        } else {
            throw new Exception('The country not found', 500);
        }
    }

    /**
     * This method is used get country struct data geography
     *
     * @param string $key
     *            key group country
     * @param array $data
     *            data country
     *            @throw if data or group country not found
     *            
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return array
     */
    private function _getCountryStruct($key, array $data = array())
    {
        if (empty($data)) {
            throw new Exception('Data is empty', 500);
        }
        $this->_setGroupData($data);
        $groups = array(
            'group1' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'city'
                ),
                '-district-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'district'
                ),
                '-ward-' => array(
                    'name' => $this->sublocality,
                    'label' => 'sublocality',
                    'type' => 'ward'
                )
            ),
            'group2' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-community-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'community'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'province'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel3,
                    'label' => 'administrative_area_level_3',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel4,
                    'label' => 'administrative_area_level_4',
                    'type' => 'city'
                )
            ),
            'group3' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'county'
                ),
                '-district-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'district'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group4' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group5' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_2',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_3',
                    'type' => 'city'
                )
            ),
            'group6' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-state-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'state'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group7' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'province'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group8' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'province'
                ),
                '-department-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'department'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group9' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'province'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group10' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'province'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'county'
                ),
                '-town-' => array(
                    'name' => $this->administrativeLevel3,
                    'label' => 'administrative_area_level_3',
                    'type' => 'town'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                ),
                '-ward-' => array(
                    'name' => $this->sublocality,
                    'label' => 'sublocality',
                    'type' => 'ward'
                )
            ),
            'group11' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'province'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'city'
                )
            ),
            'group12' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-region-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'region'
                ),
                '-district-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'district'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group13' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-region-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'region'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group14' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-region-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'region'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'province'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group15' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-region-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'region'
                ),
                '-province-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'province'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel3,
                    'label' => 'administrative_area_level_3',
                    'type' => 'city'
                )
            ),
            'group16' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-region-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'region'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'city'
                )
            ),
            'group17' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-state-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'state'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group18' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-state-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'state'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'group19' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '-state-' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'state'
                ),
                '-region-' => array(
                    'name' => $this->administrativeLevel2,
                    'label' => 'administrative_area_level_2',
                    'type' => 'region'
                ),
                '-county-' => array(
                    'name' => $this->administrativeLevel3,
                    'label' => 'administrative_area_level_3',
                    'type' => 'county'
                ),
                '/location/citytown' => array(
                    'name' => $this->locality,
                    'label' => 'locality',
                    'type' => 'city'
                )
            ),
            'default' => array(
                '/location/country' => array(
                    'name' => $this->country,
                    'label' => 'country',
                    'type' => 'country'
                ),
                '/location/citytown' => array(
                    'name' => $this->administrativeLevel1,
                    'label' => 'administrative_area_level_1',
                    'type' => 'city'
                ),
            )
        );
        if (isset($groups[$key])) {
            $group = $this->_filterGroup($groups[$key], $data);
            $groupsLookup = $group;
            foreach ($group as $k => $v) {
                if (empty($v['name'])) {
                    unset($groupsLookup[$k]);
                }
            }
            $groupData = array_merge($groupsLookup, array(
                '-route-' => isset($data['route']) ? $data['route'] : "",
                'identifier' => isset($data['identifier']) ? $data['identifier'] : "",
                'political' => isset($data['political']) ? $data['political'] : ""
            ));
            return $groupData;
        } else {
            throw new Exception('The group country not found', 500);
        }
    }

    /**
     * This method is used filter group data after set group for country
     * Using for country special
     *
     * @param array $group
     *            group data country after set
     * @param array $data
     *            data country
     *            
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return array
     */
    private function _filterGroup($group, array $data = array())
    {
        if ($this->country == 'Vietnam') {
            $centralCities = array(
                'Can Tho',
                'Da Nang',
                'Haiphong',
                'Ho Chi Minh',
                'Hanoi'
            );
            $keys = array_keys($data);
            if (in_array($this->administrativeLevel1, $centralCities)) {
                return $group;
            } else {
                if (count($keys) == 2) {
                    return array(
                        '/location/country' => array(
                            'name' => $this->country,
                            'label' => 'country',
                            'type' => 'country'
                        ),
                        '-province-' => array(
                            'name' => $this->administrativeLevel1,
                            'label' => 'administrative_area_level_1',
                            'type' => 'province'
                        )
                    );
                } else 
                    if (count($keys) > 2 && $keys[3] == 'locality') {
                        return array(
                            '/location/country' => array(
                                'name' => $this->country,
                                'label' => 'country',
                                'type' => 'country'
                            ),
                            '-province-' => array(
                                'name' => $this->administrativeLevel1,
                                'label' => 'administrative_area_level_1',
                                'type' => 'province'
                            ),
                            '/location/citytown' => array(
                                'name' => $this->locality,
                                'label' => 'locality',
                                'type' => 'city'
                            ),
                            '-ward-' => array(
                                'name' => $this->sublocality,
                                'label' => 'sublocality',
                                'type' => 'ward'
                            )
                        );
                    } else {
                        return array(
                            '/location/country' => array(
                                'name' => $this->country,
                                'label' => 'country',
                                'type' => 'country'
                            ),
                            '-province-' => array(
                                'name' => $this->administrativeLevel1,
                                'label' => 'administrative_area_level_1',
                                'type' => 'province'
                            ),
                            '-district-' => array(
                                'name' => $this->administrativeLevel2,
                                'label' => 'administrative_area_level_2',
                                'type' => 'district'
                            ),
                            '-ward-' => array(
                                'name' => $this->sublocality,
                                'label' => 'sublocality',
                                'type' => 'ward'
                            )
                        );
                    }
            }
        } else {
            return $group;
        }
    }

    /**
     * This method is used set private data group
     *
     * @param array $data
     *            data country
     *            
     * @author vietna <vietna@greenglobal.com>
     *        
     * @return array
     */
    private function _setGroupData($data)
    {
        $this->administrativeLevel1 = isset($data['administrative_area_level_1']['long_name']) ? $data['administrative_area_level_1']['long_name'] : "";
        $this->administrativeLevel2 = isset($data['administrative_area_level_2']['long_name']) ? $data['administrative_area_level_2']['long_name'] : "";
        $this->administrativeLevel3 = isset($data['administrative_area_level_3']['long_name']) ? $data['administrative_area_level_3']['long_name'] : "";
        $this->administrativeLevel4 = isset($data['administrative_area_level_4']['long_name']) ? $data['administrative_area_level_4']['long_name'] : "";
        $this->administrativeLevel5 = isset($data['administrative_area_level_5']['long_name']) ? $data['administrative_area_level_5']['long_name'] : "";
        $this->locality = isset($data['locality']['long_name']) ? $data['locality']['long_name'] : "";
        $this->sublocality = isset($data['sublocality']['long_name']) ? $data['sublocality']['long_name'] : "";
        // $this->route = isset($data['route']['long_name']) ? $data['route']['long_name'] : '';
        // $this->premise = isset($data['premise']['long_name']) ? $data['premise']['long_name'] : '';
        // $this->airport = isset($data['airport']['long_name']) ? $data['airport']['long_name'] : '';
        // $this->park = isset($data['park']['long_name']) ? $data['park']['long_name'] : '';
        // $this->point_of_interest = isset($data['point_of_interest']['long_name']) ? $data['point_of_interest']['long_name'] : '';
        // $this->establishment = isset($data['establishment']['long_name']) ? $data['establishment']['long_name'] : '';
    }
}
