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
class Mongroove_Database
{
    /**
     * Database name
     * @var string
     */
    protected $name;

    /**
     * Database connection
     * @var Mongroove_Connection
     */
    protected $connection;

    /**
     * Database handler
     * @var MongoDB
     */
    protected $dbh;

    /**
     * Class constructor.
     *
     * @param Mongroove_Connection $connection
     * @param string $name
     */
    public function __construct($connection, $name)
    {
        $this->dbh = new MongoDB($connection->getClient(), $name);
        $this->connection = $connection;
        $this->name = $name;
    }

    /**
     * Retrieves collection
     *
     * @param string $name
     * @return Mongroove_Collection
     */
    public function getCollection($name)
    {
        return new Mongroove_Collection($this, $name);
    }

    /**
     * Retrieves connection
     *
     * @return Mongroove_Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Retrieves database handler
     *
     * @return MongoDB
     */
    public function getDatabaseHandler()
    {
        return $this->dbh;
    }

    /**
     * Wrapper method for MongoDB::getReadPreference().
     *
     * For driver versions between 1.3.0 and 1.3.3, the return value will be
     * converted for consistency with {@link Mongroove_Database::setReadPreference()}.
     *
     * @see http://php.net/manual/en/mongodb.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return Mongroove_Utils_ReadPreference::convertReadPreference($this->getDatabaseHandler()->getReadPreference());
    }

    /**
     * Wrapper method for MongoDB::setReadPreference().
     *
     * @see http://php.net/manual/en/mongodb.setreadpreference.php
     * @param string $readPreference
     * @param array  $tags
     * @return boolean
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        if(isset($tags))
        {
            return $this->getDatabaseHandler()->setReadPreference($readPreference, $tags);
        }

        return $this->getDatabaseHandler()->setReadPreference($readPreference);
    }
}