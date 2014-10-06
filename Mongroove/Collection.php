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

    /**
     * Execute the findAndModify command with the update option.
     *
     * This method will dispatch preFindAndUpdate and postFindAndUpdate events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @param array $query
     * @param array $new_obj
     * @param array $options
     * @return array|null
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function findAndUpdate(array $query, array $new_obj, array $options = array())
    {
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        $command = array();
        $command['findandmodify'] = $this->getName();
        $command['query'] = (object) $query;
        $command['update'] = (object) $new_obj;
        $command = array_merge($command, $options);

        $result = $this->getDatabase()->getDatabaseHandler()->command($command);

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }

        return isset($result['value']) ? $result['value'] : null;
    }

    /**
     * Execute the findAndModify command with the remove option.
     *
     * This method will dispatch preFindAndRemove and postFindAndRemove events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @param array $query
     * @param array $options
     * @return array|null
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function findAndRemove(array $query, array $options = array())
    {
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        $command = array();
        $command['findandmodify'] = $this->getName();
        $command['query'] = (object) $query;
        $command['remove'] = true;
        $command = array_merge($command, $options);

        $result = $this->getDatabase()->getDatabaseHandler()->command($command);

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }

        return isset($result['value']) ? $result['value'] : null;
    }

    /**
     * Wrapper method for MongoCollection::findOne().
     *
     * This method will dispatch preFindOne and postFindOne events.
     *
     * @see http://php.net/manual/en/mongocollection.findone.php
     * @param array $query
     * @param array $fields
     * @return array|null
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        return $this->raw()->findOne($query, $fields);
    }

    /**
     * Wrapper method for MongoCollection::insert().
     *
     * This method will dispatch preInsert and postInsert events.
     *
     * @see http://php.net/manual/en/mongocollection.insert.php
     * @param array $a Document to insert
     * @param array $options
     * @return array|boolean
     */
    public function insert(array &$a, array $options = array())
    {
        $document = $a;
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        $result = $this->raw()->insert($document, $options);

        if(isset($document['_id']))
        {
            $a['_id'] = $document['_id'];
        }

        return $result;
    }

    /**
     * Wrapper method for MongoCollection::update().
     *
     * This method will dispatch preUpdate and postUpdate events.
     *
     * @see http://php.net/manual/en/mongocollection.update.php
     * @param array $query
     * @param array $new_obj
     * @param array $options
     * @return array|boolean
     */
    public function update($query, array $new_obj, array $options = array())
    {
        if(is_scalar($query))
        {
            trigger_error('Scalar $query argument for update() is deprecated', E_USER_DEPRECATED);
            $query = array('_id' => $query);
        }

        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        /* Allow "multi" to be used instead of "multiple", as it's accepted in
         * the MongoDB shell and other (non-PHP) drivers.
         */
        if(isset($options['multi']) && ! isset($options['multiple']))
        {
            $options['multiple'] = $options['multi'];
            unset($options['multi']);
        }

        return $this->raw()->update($query, $new_obj, $options);
    }

    /**
     * Wrapper method for MongoCollection::remove().
     *
     * This method will dispatch preRemove and postRemove events.
     *
     * @see http://php.net/manual/en/mongocollection.remove.php
     * @param array $query
     * @param array $options
     * @return array|boolean
     */
    public function remove(array $query, array $options = array())
    {
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        return $this->raw()->remove($query, $options);
    }

    /**
     * Execute the geoNear command.
     *
     * @param array $near
     * @param array $query
     * @param array $options
     * @return array
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function near($near, array $query, array $options)
    {
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        $command = array();
        $command['geoNear'] = $this->getName();
        $command['near'] = $near;
        $command['spherical'] = isset($near['type']);
        $command['query'] = (object) $query;
        $command = array_merge($command, $options);

        $result = $this->getDatabase()->getDatabaseHandler()->command($command);

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }

        return isset($result['results']) ? $result['results'] : array();
    }

    /**
     * Execute the mapReduce command.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @param string|MongoCode $map
     * @param string|MongoCode $reduce
     * @param array|string $out
     * @param array $query
     * @param array $options
     * @return array
     * @throws Mongroove_Result_Exception if the command fails
     */
    public function mapReduce($map, $reduce, $out, array $query, array $options)
    {
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        $command = array();
        $command['mapreduce'] = $this->getName();
        $command['map'] = $map;
        $command['reduce'] = $reduce;
        $command['query'] = (object) $query;
        $command['out'] = $out;
        $command = array_merge($command, $options);

        foreach(array('map', 'reduce', 'finalize') as $key)
        {
            if(isset($command[$key]) && is_string($command[$key]))
            {
                $command[$key] = new MongoCode($command[$key]);
            }
        }

        $result = $this->getDatabase()->getDatabaseHandler()->command($command);

        if(empty($result['ok']))
        {
            throw new Mongroove_Result_Exception($result);
        }

        if(isset($result['result']) && is_string($result['result']))
        {
            return $this->getDatabase()->getCollection($result['result'])->find();
        }

        if(isset($result['result']) && is_array($result['result']) &&
            isset($result['result']['db'], $result['result']['collection']))
        {
            return $this->getDatabase()->setDbName($result['result']['db'])->getCollection($result['result']['collection'])->find();
        }

        return isset($result['results']) ? $result['results'] : array();
    }

    /**
     * Execute the distinct command.
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
     * Execute the count command.
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
     * Converts "safe" write option to "w" for driver versions 1.3.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertWriteConcern(array $options)
    {
        if(version_compare(phpversion('mongo'), '1.3.0', '<'))
        {
            return $options;
        }

        if(isset($options['safe']) && ! isset($options['w']))
        {
            $options['w'] = is_bool($options['safe']) ? (integer) $options['safe'] : $options['safe'];
            unset($options['safe']);
        }

        return $options;
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