<?php

/**
 * Service model for Calendar API (v3).
 *
 * PHP Version 5
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
 *
 * Copyright (c) 2014 Rick Buczynski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Mymodules_Google_Model_Calendar_Service 
    extends Mymodules_Google_Model_Service_Abstract
    implements Mymodules_Google_Model_Service_Interface
{

    protected $_scopes = array(
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.readonly'
    );

    /**
     * Constructor.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_service = new Google_Service_Calendar($this->getClient());
    }

    /**
     * Delete an item.
     * 
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return boolean
     */
    public function delete($kind, $parameters = array())
    {
        $results = false;

        if ($this->_service instanceof Google_Service_Calendar) {
            try {
                $helper     = Mage::getSingleton('google/resource_helper_calendar');
                $resource   = $this->_service->$kind;
                // Maps operation to API client library method
                $method     = $helper->getDeleteMap($kind);

                // Calls client library method, passing in mapped parameters
                $response   = call_user_func_array(
                    array($resource, $method), 
                    $helper->getParameters($parameters, 'delete', $kind)
                );
                
                // Docs say it returns a boolean, but always seems to be null even on success
                $results    = (bool) $response;
            } catch (Exception $error) {
                $results = false;
            }
        }

        return $results;
    }

    /**
     * Fetch all result rows as a sequential array.
     *
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return array
     */
    public function fetchAll($kind = 'calendars', $parameters = array())
    {
        $results = array();

        if ($this->_service instanceof Google_Service_Calendar) {
            try {
                $helper     = Mage::getSingleton('google/resource_helper_calendar');
                $resource   = $this->_service->$kind;
                // Maps operation to API client library method
                $method     = $helper->getFetchAllMap($kind);

                // Calls client library method, passing in mapped parameters
                $response   = call_user_func_array(
                    array($resource, $method), 
                    $helper->getParameters($parameters, 'fetchAll', $kind)
                );

                // Normalizes the response for Magento-style conventions
                $results    = $this->translateKeys((array) $response->toSimpleObject());
            } catch (Exception $error) {
                $results = array();
            }
        }

        return $results;
    }

    /**
     * Fetch all result rows as an associative array.
     *
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return array
     */
    public function fetchAssoc($kind = 'calendars', $parameters = array())
    {
        return $this->fetchAll($kind, $parameters);
    }

    /**
     * Fetch the first column of all result rows as an array.
     *
     * @todo  Implement. Currently forwards to fetch all.
     * 
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return array
     */
    public function fetchCol($kind = 'calendars', $parameters = array())
    {
        return $this->fetchAll($kind, $parameters);
    }

    /**
     * Fetch the first column of the first row of the results.
     *
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return array
     */
    public function fetchOne($kind = 'calendars', $parameters = array())
    {
        $data = $this->fetchRow($kind, $parameters);

        reset($data);

        return current($data);
    }

    /**
     * Fetch all result rows as an array of key-value pairs.
     *
     * @todo  Implement. Currently forwards to fetch all.
     * 
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return array
     */
    public function fetchPairs($kind = 'calendars', $parameters = array())
    {
        return $this->fetchAll($kind, $parameters);
    }

    /**
     * Fetch the first row of the results.
     *
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return array
     */
    public function fetchRow($kind = 'calendars', $parameters = array())
    {
        $results = array();

        if ($this->_service instanceof Google_Service_Calendar) {
            try {
                $helper     = Mage::getSingleton('google/resource_helper_calendar');
                $resource   = $this->_service->$kind;
                // Maps operation to API client library method
                $method     = $helper->getFetchOneMap($kind);

                // Calls client library method, passing in mapped parameters
                $response   = call_user_func_array(
                    array($resource, $method), 
                    $helper->getParameters($parameters, 'fetchOne', $kind)
                );

                // Normalizes the response for Magento-style conventions
                $results    = $this->translateKeys((array) $response->toSimpleObject());
            } catch (Exception $error) {
                $results = array();
            }
        }

        return $results;
    }

    /**
     * Insert a record into storage.
     * 
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return mixed
     */
    public function insert($kind = 'calendars', $parameters = array())
    {
        $results = array();

        if ($this->_service instanceof Google_Service_Calendar) {
            try {
                $helper     = Mage::getSingleton('google/resource_helper_calendar');
                $resource   = $this->_service->$kind;
                // Maps operation to API client library method
                $method     = $helper->getInsertMap($kind);

                // Calls client library method, passing in mapped parameters
                $response   = call_user_func_array(
                    array($resource, $method), 
                    $helper->getParameters($parameters, 'insert', $kind)
                );

                // Normalizes the response for Magento-style conventions
                $results    = $this->translateKeys((array) $response->toSimpleObject());
            } catch (Exception $error) {
                echo '<pre>'; var_dump($error); echo '</pre>';
                $results = array();
            }
        }

        return $results;
    }

    /**
     * Execute a custom query on the service.
     * 
     * @param string $kind       The requested API resource.
     * @param string $method     The requested resource method.
     * @param array  $parameters The request parameters.
     * 
     * @return mixed
     */
    public function query($kind, $method, $parameters = array())
    {
        $results = array();

        if ($this->_service instanceof Google_Service_Calendar) {
            try {
                $resource   = $this->_service->$kind;
                $results    = call_user_func_array(array($resource, $method), $parameters);
            } catch (Exception $error) {
                $results = null;
            }
        }

        return $results;
    }

    /**
     * Translate keys in an array to a Varien_Object-compatible form.
     * 
     * @param array $array The input array.
     * 
     * @return array
     */
    public function translateKeys($array = array())
    {

        $results = array();

        foreach ($array as $key => $value) {
            $translation = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));

            if (is_array($value)) {
                $results[$translation] = $this->translateKeys($value);
            } else {
                $results[$translation] = $value;
            }
        }

        return $results;
    }

    /**
     * Update a record in storage.
     * 
     * @param  string              $kind       The requested API resource.
     * @param  array|Varien_Object $parameters The request parameters or object.
     * 
     * @return mixed
     */
    public function update($kind = 'calendars', $parameters = array())
    {
        $results = array();

        if ($this->_service instanceof Google_Service_Calendar) {
            try {
                $helper     = Mage::getSingleton('google/resource_helper_calendar');
                $resource   = $this->_service->$kind;
                // Maps operation to API client library method
                $method     = $helper->getUpdateMap($kind);

                // Calls client library method, passing in mapped parameters
                $response   = call_user_func_array(
                    array($resource, $method), 
                    $helper->getParameters($parameters, 'update', $kind)
                );

                // Normalizes the response for Magento-style conventions
                $results    = $this->translateKeys((array) $response->toSimpleObject());
            } catch (Exception $error) {
                $results = array();
            }
        }

        return $results;
    }

}