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
class Mongroove_Cursor implements Iterator, ArrayAccess
{
    /**
     * Mongo cursor handler
     * @var MongoCursor
     */
    protected $cursor;

    /**
     * Current query object
     * @var Mongroove_Query
     */
    protected $query;

    /**
     * Class constructor.
     *
     * @param Mongroove_Query $query
     * @param MongoCursor $cursor
     */
    public function __construct($query, $cursor)
    {
        $this->cursor = $cursor;
        $this->query = $query;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->cursor->next();
    }

    /**
     * Return the current element
     *
     * @return Mongroove_Document
     */
    public function current()
    {
        return $this->getQuery()
            ->getCollection()
            ->getDocument()
            ->fromArray($this->cursor->current());
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return $this->cursor->key();
    }

    /**
     * Returns cursor as array
     *
     * @return array
     */
    public function toArray()
    {
        $output = array();

        foreach($this as $item)
        {
            $output[] = $item->toArray();
        }

        return $output;
    }

    /**
     * Retrieve current collection
     *
     * @return Mongroove_Query
     */
    protected function getQuery()
    {
        return $this->query;
    }

    public function offsetExists($offset)
    {
        return $this->valid();
    }

    public function offsetGet($offset)
    {
        if(is_numeric($offset))
        {
            $this->rewind();

            for($i = 0; $i < $offset; $i++)
            {
                $this->next();
            }

            if($this->valid())
            {
                return $this->current();
            }
            else
            {
                throw new OutOfBoundsException('Offset "' . $offset . '" is out of bounds');
            }
        }
        else
        {
            throw new OutOfRangeException('Offset "' . $offset . '" must be numeric');
        }
    }

    public function offsetSet($offset, $value)
    {
        throw new Mongroove_Collection_Cursor('Cannot set cursor value');
    }

    public function offsetUnset($offset)
    {
        throw new Mongroove_Collection_Cursor('Cannot unset cursor value');
    }
}