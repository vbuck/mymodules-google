<?php

/**
 * Google services adapter abstract class.
 *
 * PHP Version 5
 *
 * @uses      Google PHP Client API Library
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

abstract class Mymodules_Google_Model_Service_Abstract 
    extends Google_Service
{

    protected $_scopes = array();
    /* @var $_service Google_Service */
    protected $_service;

    /**
     * Prepare a client session.
     *
     * @return void
     */
    public function __construct() 
    {
        $client = Mage::getSingleton('google/resource_helper_client')->getInstance($this->_scopes);

        parent::__construct($client);

        $this->_construct();
    }

    /**
     * Extending-class constructor.
     * 
     * @return void
     */
    public function _construct() { }

    /**
     * Start transaction. Not implemented.
     * 
     * @return Mymodules_Google_Model_Service_Abstract
     */
    public function beginTransaction()
    {
        return $this;
    }

    /**
     * Commit transaction. Not implemented.
     * 
     * @return Mymodules_Google_Model_Service_Abstract
     */
    public function commit()
    {
        return $this;
    }

    /**
     * Rollback transaction. Not implemented
     *
     * @return Mymodules_Google_Model_Service_Abstract
     */
    public function rollback()
    {
        return $this;
    }

    /**
     * Get adapter transaction level state. Not implemented, always 0.
     *
     * @return int
     */
    public function getTransactionLevel()
    {
        return 0;
    }

    /**
     * Get the underlying service object.
     * 
     * @return Google_Service
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Set the client instance.
     * 
     * @param Google_Client $client
     * 
     * @return Mymodules_Google_Model_Service_Abstract
     */
    public function setClient(Google_Client $client)
    {
        $this->client = $client;

        return $this;
    }

}