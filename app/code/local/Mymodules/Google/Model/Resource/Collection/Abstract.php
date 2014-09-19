<?php

/**
 * Google services collection abstract class.
 *
 * PHP Version 5
 *
 * @todo      Move Calendar API-specific rendering into calendar collection class.
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

abstract class Mymodules_Google_Model_Resource_Collection_Abstract 
    extends Varien_Data_Collection
{

    /* @var $_apiResource string */
    protected $_apiResource;
    /* @var $_data array */
    protected $_data        = null;
    protected $_maxResults  = 2500;
    protected $_parameters  = array();
    protected $_pageTokens  = array();
    
    /**
     * Identifier field name for collection items
     *
     * @var string
     */
    protected $_idFieldName;

    /**
     * Model name
     *
     * @var string
     */
    protected $_model;

    /**
     * Resource model name
     *
     * @var string
     */
    protected $_resourceModel;

    /**
     * Resource instance
     *
     * @var Mage_Core_Model_Resource_Abstract
     */
    protected $_resource;

    /**
     * Collection constructor.
     *
     * @param Mage_Core_Model_Resource_Abstract $resource The resource model.
     * 
     * @return void
     */
    public function __construct($resource = null)
    {
        parent::__construct();

        $this->_construct();
        $this->_resource = $resource;

        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Constructor.
     * 
     * @return void
     */
    public function _construct() {}

    /**
     * Set original item data.
     *
     * @todo Profile memory impact of calling afterLoad.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $item->setOrigData();
            $item->afterLoad();
        }

        Mage::dispatchEvent('core_collection_abstract_load_after', array('collection' => $this));

        return $this;
    }

    /**
     * Recursively fetch all results.
     *
     * Fetching every result is required because the API
     * has an incompatible paging mechanism. Instead, a 
     * page token is given to mark the presence of 
     * additional results.
     *
     * The token is stored in a map on the collection, and
     * each token is written to its child item, so that if
     * a specific page is requested, it can be retrieved
     * by selecting all items for that given mapped token.
     *
     * At present, this will be sufficient, as its unlikely
     * to be polling large numbers of items for a given
     * resource.
     *
     * @param string $resource The API resource.
     * 
     * @return array
     */
    protected function _fetchAll($resource = null)
    {
        if (!$resource) {
            $resource = $this->_apiResource;
        }

        // Fetch initial page
        $data   = $this->_conn->fetchAll($resource, $this->_parameters);
        $items  = $data['items'];

        // Empty token for first page
        $this->_pageTokens = array(null);

        // Continue fetching all pages
        while (isset($data['next_page_token'])) {
            $nextPageToken =
            $this->_pageTokens[] = 
            $this->_parameters['pageToken'] = $data['next_page_token'];

            $data = $this->_conn->fetchAll($resource, $this->_parameters);

            // Write the page token to each item for mapping to self::$_pageTokens
            foreach ($data['items'] as $key => $value) {
                $data['items'][$key]['page_token'] = $nextPageToken;
            }

            $items = array_merge($items, $data['items']);
        }

        unset($this->_parameters['pageToken']);

        return $items;
    }

    /**
     * Standard resource collection initalization
     *
     * @param string $model    The model alias.
     * @param string $resource The API resource.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _init($model, $resource)
    {
        $this->setModel($model);
        $this->setResourceModel($model);

        $this->_apiResource = $resource;

        return $this;
    }

    /**
     * Backend data loader.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _loadData()
    {
        if ($this->_data === null) {
            // Fetch the data
            $this->_data = $this->_fetchAll();

            // Post-fetch operations
            if (is_array($this->_data)) {
                $this->_renderOrders(); // Ordering is post-fetch

                foreach ($this->_data as $row) {
                    $item = $this->getNewEmptyItem();

                    if ($this->getIdFieldName()) {
                        $item->setIdFieldName($this->getIdFieldName());
                    }

                    $item->addData($row);
                    $this->addItem($item);
                }
            }

            $this->resetData();
        }

        return $this;
    }

    /**
     * Render the current page. Must happen post-load,
     * because it will reduce the entire collection to
     * those matching the given page token.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _renderCurPage()
    {
        if ($this->_curPage) {
            $items          = $this->getItemsByColumnValue('page_token', $this->_pageTokens[($this->_curPage - 1)]);
            $this->_items   = null;

            foreach($items as $item)
            {
                $this->addItem($item);
            }
        }

        // Correct empty data
        if (!$this->_items) {
            $this->_items = array();
        }

        return $this;
    }

    /**
     * Render the filters.
     * 
     * @return  Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _renderFilters()
    {
        foreach ($this->_filters as $filter) {
            $this->_parameters[$filter['field']] = $filter['value'];
        }

        return $this;
    }

    /**
     * Render any post-fetch filters.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _renderFiltersAfter()
    {
        return $this;
    }

    /**
     * Render collection limits.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _renderLimit()
    {
        if (!$this->_pageSize || $this->_pageSize > $this->_maxResults) {
            $this->_pageSize = $this->_maxResults;
        }

        $this->_parameters['maxResults'] = $this->_pageSize;

        /**
         * Current page depends on page token in response,
         * so we can't render it yet. Instead, the entire
         * collection is fetched recursively, and all
         * tokens are mapped to a page number.
         *
         * @see Mymodules_Google_Model_Resource_Collection_Abstract::_renderCurPage
         */

        return $this;
    }

    /**
     * Re-order the collection. Must run post-load.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _renderOrders()
    {
        if ($this->_data) {
            $parameters = array();

            foreach ($this->_orders as $field => $direction) {
                $sort = array();

                for ($i = 0; $i < count($this->_data); $i++) {
                    $sort[$i] = $this->_data[$i][$field];
                }

                $parameters[] = &$sort;
                $parameters[] = &$direction;
            }

            if (count($parameters) > 1) {
                $parameters[] = &$this->_data;

                call_user_func_array('array_multisort', $parameters);
            }
        }

        return $this;
    }

    /**
     * Specify collection object ID field name.
     *
     * @param string $fieldName The ID field name.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _setIdFieldName($fieldName)
    {
        $this->_idFieldName = $fieldName;
        return $this;
    }

    /**
     * Delete all items in the collection.
     *
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function delete()
    {
        foreach ($this->getItems() as $item) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Get the connection object.
     * 
     * @return mixed
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    /**
     * Get a stored filter by its field.
     * 
     * @param string $field The field being filtered.
     * 
     * @return array|null
     */
    public function getFilter($field)
    {
        foreach ($this->_filters as $filter) {
            if ($filter['field'] === $field) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * Id field name getter.
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Get the model instance.
     *
     * @param array $args Unknown.
     * 
     * @return string
     */
    public function getModelName($args = array())
    {
        return $this->_model;
    }

    /**
     * Get the resource instance.
     *
     * @return Mage_Core_Model_Resource_Abstract
     */
    public function getResource()
    {
        if (empty($this->_resource)) {
            $this->_resource = Mage::getResourceModel($this->getResourceModelName());
        }

        return $this->_resource;
    }

    /**
     * Get the resource model name.
     * 
     * @return string
     */
    public function getResourceModelName()
    {
        return $this->_resourceModel;
    }

    /**
     * Front data loader.
     *
     * @param boolean $printQuery Flag to print the query. Not implemented.
     * @param boolean $logQuery   Flag to log the query. Not implemented.
     * 
     * @return  Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->clear();

        $this->_renderFilters()
             ->_renderLimit();

        $this->_loadData();
        $this->_setIsLoaded();

        $this->_renderFiltersAfter()
            ->_renderCurPage();

        $this->_afterLoad();

        return $this;
    }

    /**
     * Reset the temporary data store.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function resetData()
    {
        $this->_data = null;

        return $this;
    }

    /**
     * Save all items in the collection.
     *
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function save()
    {
        foreach ($this->getItems() as $item) {
            $item->save();
        }

        return $this;
    }

    /**
     * Set the connection object.
     * 
     * @param mixed $conn The connection object.
     *
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function setConnection($conn)
    {
        $this->_conn = $conn;

        return $this;
    }

    /**
     * Set the model name for collection items.
     *
     * @param string $model The model alias.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function setModel($model)
    {
        if (is_string($model)) {
            $this->_model = $model;
            $this->setItemObjectClass(Mage::getConfig()->getModelClassName($model));
        }

        return $this;
    }

    /**
     * Add a sort order to the collection.
     * 
     * @param string $field     The field by which to order the collection.
     * @param string $direction The direction in which to order the collection.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        /**
         * Translate Magento sort codes to PHP sort codes.
         *
         * @see http://www.php.net/manual/en/function.array-multisort.php
         */
        switch (strtoupper($direction)) {
            case self::SORT_ORDER_ASC:
                $direction = SORT_ASC;
                break;
            case self::SORT_ORDER_DESC:
            default:
                $direction = SORT_DESC;
                break;
        }

        $this->_orders[$field] = $direction;

        return $this;
    }

    /**
     * Set the resource model name.
     * 
     * @param string $model The resource model alias.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    public function setResourceModel($model)
    {
        $this->_resourceModel = $model;

        return $this;
    }

}