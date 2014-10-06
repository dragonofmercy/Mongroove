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
     * Parent collection
     * @var Mongroove_Collection
     */
    protected $collection;

    /**
     * @var array
     */
    private $fields = array();

    /**
     * Class constructor.
     *
     * @param Mongroove_Collection|null $collection
     * @param Mongroove_Connection|null $connection
     * @throws InvalidArgumentException
     */
    public function __construct($collection = null, $connection = null)
    {
        if(!is_null($collection) && $collection instanceof Mongroove_Collection)
        {
            $this->collection = $collection;
        }
        else
        {
            throw new InvalidArgumentException('Cannot create a document without collection');
        }
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
        return $this->getFields();
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
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if($this->offsetExists($offset))
        {
            return $offset;
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
        $this->fields[$offset] = $value;
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
     * Retrieve all fields
     */
    protected function getFields()
    {
        return $this->fields;
    }

    /**
     * Setup document
     */
    protected function setup()
    {
    }
}