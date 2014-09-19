<?php

/**
 * Interface for Google services adapters.
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

interface Mymodules_Google_Model_Service_Interface
{

    /**
     * Fetch all result rows as a sequential array.
     * 
     * @return array
     */
    public function fetchAll();

    /**
     * Fetch all result rows as an associative array.
     * 
     * @return array
     */
    public function fetchAssoc();

    /**
     * Fetch the first column of all result rows as an array.
     * 
     * @return array
     */
    public function fetchCol();

    /**
     * Fetch the first column of the first row of the results.
     * 
     * @return mixed
     */
    public function fetchOne();

    /**
     * Fetch all result rows as an array of key-value pairs.
     * 
     * @return array
     */
    public function fetchPairs();

    /**
     * Fetch the first row of the results.
     * 
     * @return array
     */
    public function fetchRow();

    /**
     * Get the underlying service object.
     * 
     * @return Google_Service
     */
    public function getService();

}