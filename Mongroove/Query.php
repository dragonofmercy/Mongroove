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
class Mongroove_Query
{
    /**
     * Collection where executing the query
     * @var Mongroove_Collection
     */
    protected $collection;

    /**
     * Query vars
     * @var array
     */
    protected $query = array();

    /**
     * Fields vars
     * @var array
     */
    protected $fields = array();

    /**
     * Class constructor.
     *
     * @param Mongroove_Collection $collection
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Execute query
     *
     * @return Mongroove_Cursor
     */
    public function execute()
    {
        $this->getCollection()->setQuery($this);
        return $this->find();
    }

    /**
     * Queries this collection, returning a Mongroove_Cursor for the result set
     *
     * @return Mongroove_Cursor
     */
    protected function find()
    {
        return new Mongroove_Cursor($this, $this->getCollection()->raw()->find($this->query, $this->fields));
    }

    /**
     * Retrieve collection
     *
     * @return Mongroove_Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Short method to create a new query
     *
     * @param string $collection
     * @param Mongroove_Connection|null $connection
     * @return Mongroove_Query
     * @throws Mongroove_Manager_Exception
     */
    public static function create($collection, $connection = null)
    {
        $manager = Mongroove_Manager::getInstance();
        $connection = is_null($connection) ? $manager->getCurrentConnection() : $manager->getConnection($connection);
        return new self($connection->getDatabase()->getCollection($collection));
    }
}