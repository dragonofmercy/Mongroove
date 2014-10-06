<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the new BSD license.
 *
 * @package     Mongroove
 * @author      David Zeller <me@zellerda.com>
 * @license     http://www.opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       1.0
 */
class Mongroove_Connection
{
    /**
     * Manager instance
     * @var Mongroove_Manager
     */
    protected $manager;

    /**
     * Connection configuration array
     * @var array
     */
    protected $config;

    /**
     * Name of the connection
     * @var string
     */
    protected $name;

    /**
     * Mongo DB client
     * @var MongoClient
     */
    protected $client;

    /**
     * Class constructor.
     *
     * @param Mongroove_Manager $manager
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param string $name
     * @throws Mongroove_Connection_Exception
     */
    public function __construct($manager, $dsn, $username = null, $password = null, $name)
    {
        if(!extension_loaded('mongo'))
        {
            throw new Mongroove_Exception('The extension php_mongo is not loaded');
        }

        $this->manager = $manager;
        $this->prepare($dsn, $username, $password);
        $this->setName($name);

        try
        {
            $dsn = $this->getConnectionString();
            $options = $this->getManager()->getAttribute(Mongroove_Core::ATTR_MONGO_CLIENT_CONFIG);
            $options['connect'] = true;

            $this->client = @new MongoClient($dsn, $options);
        }
        catch(MongoConnectionException $e)
        {
            throw new Mongroove_Connection_Exception($e->getMessage());
        }
    }

    /**
     * Retrieves connection string from config
     *
     * @return string
     */
    protected function getConnectionString()
    {
        $connection_string = 'mongodb://';

        if($this->config['username'] || $this->config['password'])
        {
            if($this->config['username'])
            {
                $connection_string.= $this->config['username'] . ':';

                if($this->config['password'])
                {
                    $connection_string.= $this->config['password'];
                }
            }

            $connection_string.= '@';
        }

        $connection_string.= $this->config['host'];
        $connection_string.= '/';
        $connection_string.= $this->config['dbname'];

        return $connection_string;
    }

    /**
     * Prepares configuration from DSN
     *
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @throws Mongroove_Connection_Exception
     */
    protected function prepare($dsn, $username = null, $password = null)
    {
        if(preg_match('/host\=([.\w]+)/', $dsn, $matches))
        {
            $this->config['host'] = $matches[1];
        }
        else
        {
            throw new Mongroove_Connection_Exception('Host not found in DSN');
        }

        if(preg_match('/dbname\=([.\w]+)/', $dsn, $matches))
        {
            $this->config['dbname'] = $matches[1];
        }
        else
        {
            throw new Mongroove_Connection_Exception('DB name not found in DSN');
        }

        if($password && !$username)
        {
            throw new Mongroove_Connection_Exception('You cannot set a password without username');
        }
        else
        {
            $this->config['username'] = $username;
            $this->config['password'] = $password;
        }
    }

    /**
     * Sets connection name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retrieves database
     *
     * @return Mongroove_Database
     */
    public function getDatabase()
    {
        $classname = $this->getManager()->getAttribute(Mongroove_Core::ATTR_CLASS_DATABASE);
        return new $classname($this, $this->config['dbname']);
    }

    /**
     * Retrieves manager
     *
     * @return Mongroove_Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Retrieves MongoClient
     *
     * @return MongoClient|Mongo
     */
    public function getClient()
    {
        return $this->client;
    }
}