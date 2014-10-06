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
 */

require_once 'Exception.php';

/**
 * @package     Mongroove
 * @author      David Zeller <me@zellerda.com>
 * @license     http://www.opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       1.0
 */
class Mongroove_Core
{
    const PEAR_NAMESPACE = "Mongroove";
    const VERSION = '0.0.1';

    const ATTR_MONGO_CLIENT_CONFIG = '1';

    const ATTR_CLASS_CONNECTION = '20';
    const ATTR_CLASS_DOCUMENT = '21';
    const ATTR_CLASS_DATABASE = '22';
    const ATTR_CLASS_CURSOR = '23';
    const ATTR_CLASS_QUERY = '24';
    const ATTR_CLASS_COLLECTION = '25';
    const ATTR_CLASS_QUERY_BUILDER = '26';
    const ATTR_CLASS_QUERY_EXPR = '27';

    /**
     * Class constructor
     *
     * @throws Mongroove_Exception
     */
    public function __construct()
    {
        throw new Mongroove_Exception('Mongroove_Core is static class. No instances can be created.');
    }

    /**
     * Retrieves a collection
     *
     * @param string $collection Collection name
     * @param string|null $connection Connection name
     * @return Mongroove_Collection
     * @throws Mongroove_Collection_Exception
     * @throws Mongroove_Manager_Exception
     */
    public static function getCollection($collection, $connection = null)
    {
        $manager = Mongroove_Manager::getInstance();
        $connection = is_null($connection) ? $manager->getCurrentConnection() : $manager->getConnection($connection);
        return $connection->getDatabase()->getCollection($collection);
    }

    /**
     * Simple autoload function
     *
     * @param string $classname
     */
    public static function autoload($classname)
    {
        $separator = '_';

        if(strpos($classname, '\\') != false)
        {
            $separator = '\\';
        }

        if(0 !== stripos($classname, self::PEAR_NAMESPACE . $separator) || class_exists($classname, false) || interface_exists($classname, false))
        {
            return;
        }

        $classname = preg_replace('/^' . self::PEAR_NAMESPACE . $separator . '/', '', $classname);
        $class = str_replace($separator, DIRECTORY_SEPARATOR, $classname) . '.php';
        $class = rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $class;

        if(!include_once $class)
        {
            throw new Mongroove_Exception("Autoload cannot find $class");
        }
    }
}