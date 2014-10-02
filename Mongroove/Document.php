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
class Mongroove_Document implements ArrayAccess
{
    /**
     * Document real name
     * @var string|null
     */
    private $document_name;

    /**
     * Parent collection
     * @var Mongroove_Collection
     */
    private $collection;

    /**
     * Array of references
     * @var array
     */
    private $references = array();

    /**
     * @var array
     */
    private $fields = array();

    /**
     * Class constructor.
     *
     * @param Mongroove_Collection|null $collection
     * @param Mongroove_Connection|null $connection
     */
    public function __construct($collection = null, $connection = null)
    {
        if(!is_null($collection) && $collection instanceof Mongroove_Collection)
        {
            $this->collection = $collection;
        }
        else
        {
            $classname = get_class($this);
            $this->collection = Mongroove::getCollection($classname, $connection);
        }

        $r = new ReflectionObject($this);
        foreach($r->getProperties(ReflectionProperty::IS_PROTECTED) as $column)
        {
            $this->fields[$column->getName()] = null;
        }

        $this->setup();
    }

    /**
     * Get document name
     *
     * @return string
     */
    public function getDocumentName()
    {
        if(is_null($this->document_name))
        {
            $this->updateDocumentName(get_class($this));
        }

        return $this->document_name;
    }

    /**
     * Update document name
     *
     * @param string $name
     */
    public function updateDocumentName($name)
    {
        $this->document_name = $name;
    }

    /**
     * Hydrate the object from array
     *
     * @param array $arr
     * @return Mongroove_Document
     */
    public function fromArray(array $arr)
    {
        foreach($arr as $field => $value)
        {
            if($field == '_id')
            {
                $field = 'id';
            }

            $this->offsetSet($field, $value);
        }

        return $this;
    }

    /**
     * Retrieve the document as array
     *
     * @return array
     */
    public function toArray()
    {
        $output = array();

        foreach($this->getFields() as $field => $value)
        {
            $value = $this->parseObjectValue($value, $field);

            if(is_array($value))
            {
                $value = $this->toArrayDeep($value);
            }

            $output[$field] = $value;
        }

        return $output;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        if(array_key_exists($offset, $this->getFields()))
        {
            return true;
        }

        return false;
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     * @param boolean $array true if array output
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset, $array = false)
    {
        if($this->offsetExists($offset))
        {
            return $this->searchReferences($this->fields[$offset], $array);
        }
        elseif(Mongroove_Manager::getInstance()->getAttribute(Mongroove_Core::ATTR_FORCE_MODEL_USAGE))
        {
            throw new Mongroove_Document_Exception("The field \"$offset\" was not found");
        }

        return null;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if(!Mongroove_Manager::getInstance()->getAttribute(Mongroove_Core::ATTR_FORCE_MODEL_USAGE))
        {
            $this->fields[$offset] = $value;
        }
        else
        {
            if($this->offsetExists($offset))
            {
                $this->fields[$offset] = $value;
            }
        }
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset))
        {
            unset($this->fields[$offset]);
        }
    }

    /**
     * Retrieve document collection
     *
     * @return Mongroove_Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Retrieve all fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Checks if reference is defined
     *
     * @param string $name
     * @return boolean|string
     */
    public function hasReference($name)
    {
        foreach($this->references as $field => $config)
        {
            if(array_key_exists('doc', $config))
            {
                if($config['doc'] == $name)
                {
                    return $field;
                }
            }
        }

        return false;
    }

    /**
     * Field getter
     *
     * @param string $offset
     * @return mixed
     */
    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    /**
     * Field setter
     *
     * @param string $offset
     * @param mixed $value
     */
    public function __set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * Find array deep and load references
     *
     * @param array $value
     * @return array
     * @throws Mongroove_Document_Exception
     */
    protected function toArrayDeep($value)
    {
        $output = array();

        foreach($value as $key => $val)
        {
            $val = $this->parseObjectValue($val);

            if(is_array($val))
            {
                $val = $this->toArrayDeep($val);
            }

            $output[$key] = $val;
        }

        return $output;
    }

    /**
     * Find reference document
     *
     * @param array $reference
     * @return Mongroove_Document
     * @throws Mongroove_Document_Exception
     */
    protected function loadReference(array $reference)
    {
        $ref = $this->retrieveObjectOfReference($reference['$ref']);

        if($ref)
        {
            /** @var Mongroove_Document $ref_object */
            $ref_object = new $ref($this->getCollection(), $this->getCollection()->getDatabase()->getConnection());
            $ref_object->fromArray(MongoDBRef::get($this->getCollection()->getDatabase()->getDatabaseHandler(), $reference));

            return $ref_object;
        }
        else
        {
            return $reference;
        }
    }

    /**
     * Add reference
     *
     * @param string $ref
     * @param string $document_class
     * @throws Mongroove_Document_Exception
     */
    protected function addReference($ref, $document_class)
    {
        if($this->offsetExists($ref))
        {
            $this->references[$ref] = $document_class;
        }
        else
        {
            throw new Mongroove_Document_Exception("Cannot set reference \"$ref\", the field is not declared in document");
        }
    }

    /**
     * Match reference to document declaration
     *
     * @param string $ref
     * @return string
     * @throws Mongroove_Document_Exception
     */
    protected function retrieveObjectOfReference($ref)
    {
        $field = $this->hasReference($ref);

        if($field)
        {
            if(array_key_exists('class', $this->references[$field]))
            {
                return $this->references[$field]['class'];
            }
            else
            {
                return Mongroove_Inflector::camelize($this->references[$field]['doc']);
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Parse object value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function parseObjectValue($value)
    {
        if($value instanceof MongoId)
        {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Search object references and load it if necessary
     *
     * @param mixed $value
     * @param boolean $array
     * @return Mongroove_Document
     */
    protected function searchReferences($value, $array = false)
    {
        if(is_array($value) && !$array)
        {
            if(array_key_exists('$ref', $value) && array_key_exists('$id', $value))
            {
                return $this->loadReference($value);
            }
            else
            {
                if(count($value))
                {
                    $keys = array_keys($value);
                    if(is_numeric($keys[0]))
                    {
                        $refs = array();

                        foreach($value as $val)
                        {
                            if(is_array($val) && array_key_exists('$ref', $val) && array_key_exists('$id', $val))
                            {
                                $refs[] = $this->loadReference($val);
                            }
                        }

                        $value = $refs;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Setup document
     */
    protected function setup()
    {
    }
}