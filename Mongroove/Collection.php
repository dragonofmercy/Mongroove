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
 * @package     Mangroove
 * @author      David Zeller <me@zellerda.com>
 * @license     http://www.opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       1.0
 */
class Mongroove_Collection
{
    /**
     * DB Object
     * @var Mongroove_Database
     */
    protected $db;

    /**
     * The document object
     * @var Mongroove_Document
     */
    protected $document;

    /**
     * Collection handler
     * @var MongoCollection
     */
    protected $raw;

    /**
     * Class constructor.
     *
     * @param Mongroove_Database $db
     * @param string $collection Collection name
     */
    public function __construct($db, $collection)
    {
        $this->db = $db;
        $this->createDocument($collection);
        $this->raw = new MongoCollection($db->getDatabaseHandler(), $collection);
    }

    /**
     * Retrieves database
     *
     * @return Mongroove_Database
     */
    public function getDatabase()
    {
        return $this->db;
    }

    /**
     * Retrieves collection handler
     *
     * @return MongoCollection
     */
    public function raw()
    {
        return $this->raw;
    }

    /**
     * Retrieves document
     *
     * @return Mongroove_Document
     */
    public function getDocument()
    {
        return clone $this->document;
    }

    /**
     * Retrieve collection name
     *
     * @return string
     */
    public function getName()
    {
        return $this->raw()->getName();
    }

    /**
     * Create a new query builder
     *
     * @return Mongroove_Query_Builder
     */
    public function createQuery()
    {
        return new Mongroove_Query_Builder($this);
    }

    /**
     * Wrapper method for MongoCollection::find().
     *
     * This method will dispatch preFind and postFind events.
     *
     * @see http://php.net/manual/en/mongocollection.find.php
     * @param array $query
     * @param array $fields
     * @return MongoCursor
     */
    public function find(array $query = array(), array $fields = array())
    {
        return $this->raw()->find($query, $fields);
    }

    public function findAndUpdate()
    {
        return array();
    }

    public function findAndRemove()
    {
        return array();
    }

    public function insert()
    {
        return array();
    }

    public function update()
    {
        return array();
    }

    public function remove()
    {
        return array();
    }

    public function near()
    {

    }

    public function mapReduce()
    {

    }

    /**
     * Invokes the distinct command.
     *
     * @see http://php.net/manual/en/mongocollection.distinct.php
     * @see http://docs.mongodb.org/manual/reference/command/distinct/
     * @param string $field
     * @param array $query
     * @param array $options
     * @return array
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function distinct($field, array $query = array(), array $options = array())
    {
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        $command = array();
        $command['distinct'] = $this->getName();
        $command['key'] = $field;
        $command['query'] = (object) $query;
        $command = array_merge($command, $options);

        $result = $this->getDatabase()->getDatabaseHandler()->command($command);

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }
        else
        {
            return isset($result['result']) ? $result['result'] : array();
        }
    }

    /**
     * Execute the aggregate command.
     *
     * @see http://php.net/manual/en/mongocollection.aggregate.php
     * @see http://docs.mongodb.org/manual/reference/command/aggregate/
     * @param array $pipeline Array of pipeline operators, or the first operator
     * @param array $op,...   Additional operators (if $pipeline was the first)
     * @return array
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function aggregate(array $pipeline /* , array $op, ... */)
    {
        if(!array_key_exists(0, $pipeline))
        {
            $pipeline = func_get_args();
        }

        $command = array();
        $command['aggregate'] = $this->getName();
        $command['pipeline'] = $pipeline;

        $result = $this->getDatabase()->getDatabaseHandler()->command($command);

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }
        else
        {
            return isset($result['result']) ? $result['result'] : array();
        }
    }

    /**
     * Drops the collection.
     *
     * @return array
     */
    public function drop()
    {
        $this->raw()->drop();
    }

    /**
     * Invokes the count command.
     *
     * @see http://php.net/manual/en/mongocollection.count.php
     * @see http://docs.mongodb.org/manual/reference/command/count/
     * @param array $query
     * @param integer $limit
     * @param integer $skip
     * @return integer
     */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        return $this->raw()->count($query, $limit, $skip);
    }

    /**
     * Execute the group command
     *
     * @param array|string|MongoCode $keys
     * @param array $initial
     * @param string|MongoCode $reduce
     * @param array $options
     * @return ArrayIterator
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        $command = array();
        $command['ns'] = $this->getName();
        $command['initial'] = (object) $initial;
        $command['$reduce'] = $reduce;

        if(is_string($keys) || $keys instanceof MongoCode)
        {
            $command['$keyf'] = $keys;
        }
        else
        {
            $command['key'] = $keys;
        }

        $command = array_merge($command, $options);

        foreach(array('$keyf', '$reduce', 'finalize') as $key)
        {
            if(isset($command[$key]) && is_string($command[$key]))
            {
                $command[$key] = new MongoCode($command[$key]);
            }
        }

        if(isset($command['cond']) && is_array($command['cond']))
        {
            $command['cond'] = (object) $command['cond'];
        }

        $result = $this->getDatabase()->getDatabaseHandler()->command(array('group' => $command));

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }
        else
        {
            return $result['retval'];
        }
    }

    /**
     * Checks if document exists
     *
     * @return string
     * @throws Mongroove_Document_Exception
     */
    protected function createDocument()
    {
        $class = $this->getDatabase()->getConnection()->getManager()->getAttribute(Mongroove_Core::ATTR_CLASS_DOCUMENT);
        $this->document = new $class($this, $this->getDatabase()->getConnection());
    }

    /**
     * Convert "wtimeout" write option to "wTimeoutMS" for driver version
     * 1.5.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertWriteTimeout(array $options)
    {
        if(version_compare(phpversion('mongo'), '1.5.0', '<'))
        {
            return $options;
        }

        if(isset($options['wtimeout']) && ! isset($options['wTimeoutMS']))
        {
            $options['wTimeoutMS'] = $options['wtimeout'];
            unset($options['wtimeout']);
        }

        return $options;
    }

    /**
     * Convert "timeout" write option to "socketTimeoutMS" for driver version
     * 1.5.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertSocketTimeout(array $options)
    {
        if(version_compare(phpversion('mongo'), '1.5.0', '<'))
        {
            return $options;
        }

        if(isset($options['timeout']) && ! isset($options['socketTimeoutMS']))
        {
            $options['socketTimeoutMS'] = $options['timeout'];
            unset($options['timeout']);
        }

        return $options;
    }
}