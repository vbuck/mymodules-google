<?php

/**
 * Google services client resource helper.
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

class Mymodules_Google_Model_Resource_Helper_Client
{

    const CONFIG_PATH       = 'google';

    private $_privateKey    = null;

    public function __construct()
    {
        $this->_privateKey = $this->getPrivateKeyPath();
    }

    /**
     * Fetch, authorize, and store the access token on the client.
     * 
     * @param Google_Client $client         The client object instance.
     * @param string        $serviceAccount The service account address.
     * @param array         $scopes         The requested scopes for use.
     * @param string        $keyPath        The path to the private key.
     * 
     * @return Mymodules_Google_Model_Resource_Helper_Client
     */
    protected function _getAccessToken(
        Google_Client $client, 
        $serviceAccount = null, 
        $scopes         = array(), 
        $keyPath        = null
    )
    {
        if ( ($token = $this->getServiceToken($client)) ) {
            $client->setAccessToken($token);
        }

        $credentials = $this->getCredentials($serviceAccount, $scopes, $keyPath);

        /**
         * If your scenario requires, you can optionally provide
         * sub-credentials, on whose behalf your service account
         * will be making requests.
         */
        //$credentials->sub = 'your@email.com';

        $client->setAssertionCredentials($credentials);
        
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()
                ->refreshTokenWithAssertion($credentials);
        }

        // Write token to client instance session
        $this->setServiceToken($client, $client->getAccessToken());

        return $this;
    }

    /**
     * Config data getter.
     * 
     * @param string $key The config key.
     * 
     * @return mixed
     */
    public function getConfigData($key = '')
    {
        return Mage::getStoreConfig(self::CONFIG_PATH . '/' . $key);
    }

    /**
     * Get assertion credentials for the client session.
     * 
     * @param string $serviceAccount The service account address.
     * @param array  $scopes         The requested scopes for use.
     * @param string $keyPath        The path to the private key.
     * 
     * @return Google_Auth_AssertionCredentials
     */
    public function getCredentials($serviceAccount = null, $scopes = array(), $keyPath = null)
    {
        if (!$keyPath) {
            $keyPath = $this->_privateKey;
        }

        $key = '';
        if (is_file($keyPath)) {
            try {
                $key = file_get_contents($keyPath);
            } catch(Exception $error) {
                $key = '';
            }
        }

        return new Google_Auth_AssertionCredentials($serviceAccount, $scopes, $key);
    }

    /**
     * Create a new client instance for service interaction.
     * 
     * @param array  $scopes         The requested scopes for use.
     * @param string $appName        The registered app name for this request.
     * @param string $clientId       The client ID credential.
     * @param string $serviceAccount The service account address.
     * @param string $keyPath        The path to the private key.
     * 
     * @return Google_Client
     */
    public function getInstance(
        $scopes         = null, 
        $appName        = null, 
        $clientId       = null, 
        $serviceAccount = null, 
        $keyPath        = null
    )
    {
        $instance = new Google_Client($this->getConfigData('backend/config_ini'));

        if (!$scopes || !count($scopes)) {
            $scopes = explode(',', $this->getConfigData('backend/scopes'));
        }

        if (!$appName) {
            $appName = $this->getConfigData('api_setup/default_app');
        }

        if (!$clientId) {
            $clientId = $this->getConfigData('api_setup/service_client_id');
        }

        if (!$serviceAccount) {
            $serviceAccount = $this->getConfigData('api_setup/service_email');
        }

        $instance->setApplicationName($appName);
        $instance->setClientId($clientId);

        $this->_getAccessToken($instance, $serviceAccount, $scopes, $keyPath);

        return $instance;
    }

    /**
     * Get the path to the private key store.
     * 
     * @return mixed
     */
    public function getPrivateKeyPath()
    {
        $path = 
            Mage::getModuleDir('etc', 'Mymodules_Google') . DIRECTORY_SEPARATOR . 
            $this->getConfigData('backend/private_key');

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Get the client service token from the session is available.
     * 
     * @param Google_Client $client The client object instance.
     * 
     * @return mixed
     */
    protected function getServiceToken(Google_Client $client)
    {
        return Mage::getSingleton('core/session')->getData('google_token');//spl_object_hash($client));
    }

    /**
     * Write the service token to the client session.
     * 
     * @param Google_Client $client The client object instance.
     * @param string        $token  The service token for this request.
     *
     * @return void
     */
    protected function setServiceToken(Google_Client $client, $token)
    {
        Mage::getSingleton('core/session')->setData('google_token', $token);//spl_object_hash($client), $token);
    }

}