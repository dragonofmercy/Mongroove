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
class Mongroove_Manager extends Mongroove_Configurable
{
    /**
     * Class instance
     * @var Mongroove_Manager
     */
    protected static $_instance;

    /**
     * Array of database connections
     * @var array[Mongroove_Connection]
     */
    protected $connections = array();

    /**
     * Connection index
     * @var integer
     */
    protected $connection_index = 0;

    /**
     * Current connection index
     * @var integer
     */
    protected $current_connection_index = 0;


    /**
     * Class constructor
     *
     * @throws Mongroove_Manager_Exception
     */
    private function __construct()
    {
    }

    /**
     * Retrieves database connection
     *
     * @param string $name
     * @return Mongroove_Connection
     * @throws Mongroove_Manager_Exception
     */
    public function getConnection($name)
    {
        if(!isset($this->connections[$name]))
        {
            throw new Mongroove_Manager_Exception("Unknown connection \"$name\"");
        }

        return $this->connections[$name];
    }

    /**
     * Retrieves the current connection
     *
     * @return Mongroove_Connection
     * @throws Mongroove_Manager_Exception
     */
    public function getCurrentConnection()
    {
        if(!isset($this->connections[$this->current_connection_index]))
        {
            throw new Mongroove_Manager_Exception('There is no open connection');
        }

        return $this->getConnection($this->current_connection_index);
    }

    /**
     * Open a new connection
     *
     * @param string $dsn Connection string
     * @param string|null $username Username
     * @param string|null $password Password
     * @param string|null $name Connection name
     * @param boolean $set_current Set the connection has current
     * @return mixed
     */
    public function openConnection($dsn, $username = null, $password = null, $name = null, $set_current = true)
    {
        if(is_null($name))
        {
            $name = $this->connection_index;
            $this->connection_index++;
        }
        else
        {
            $name = (string) $name;
            if(isset($this->connections[$name]))
            {
                if($set_current)
                {
                    $this->current_connection_index = $name;
                }

                return $this->getConnection($name);
            }
        }

        $class = $this->getAttribute(Mongroove_Core::ATTR_CLASS_CONNECTION);
        /** @var Mongroove_Connection $connection */
        $connection = new $class($this, $dsn, $username, $password, $name);
        $this->connections[$name] = $connection;

        if($set_current)
        {
            $this->current_connection_index = $name;
        }

        return $this->getConnection($name);
    }

    /**
     * Retrieves manager instance
     *
     * @return Mongroove_Manager
     */
    public static function getInstance()
    {
        if(!isset(self::$_instance))
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}