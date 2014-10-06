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
abstract class Mongroove_Operation
{
    /**
     * Pipeline stages
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#stages
     */
    const GEO = '$geoNear';
    const GROUP = '$group';
    const LIMIT = '$limit';
    const MATCH = '$match';
    const OUT = '$out';
    const PROJECT = '$project';
    const REDACT = '$redact';
    const SKIP = '$skip';
    const SORT = '$sort';
    const UNWIND = '$unwind';

    /**
     * Global expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#boolean-expressions
     */
    const EXP_BOOL_AND = '$and';
    const EXP_BOOL_NOT = '$not';
    const EXP_BOOL_OR = '$or';

    /**
     * Global expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#set-expressions
     */
    const EXP_ALL = '$allElementsTrue';
    const EXP_ANY = '$anyElementTrue';
    const EXP_DIFF = '$setDifference';
    const EXP_EQUALS = '$setEquals';
    const EXP_INTERSEC = '$setIntersection';
    const EXP_SUBSET = '$setIsSubset';
    const EXP_UNION = '$setUnion';

    /**
     * Comparaison expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#comparison-expressions
     */
    const EXP_COMP_CMP = '$cmp';
    const EXP_COMP_EQUAL = '$eq';
    const EXP_COMP_GRATHER = '$gt';
    const EXP_COMP_GRATHER_EQUAL = '$gte';
    const EXP_COMP_LESS = '$lt';
    const EXP_COMP_LESS_EQUAL = '$lte';
    const EXP_COMP_NOT = '$ne';

    /**
     * Arithmetic expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#arithmetic-expressions
     */
    const EXP_MATH_ADD = '$add';
    const EXP_MATH_DIVIDE = '$divide';
    const EXP_MATH_MOD = '$mod';
    const EXP_MATH_MULTIPLY = '$multiply';
    const EXP_MATH_SUBTRACT = '$subtract';

    /**
     * String expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#string-expressions
     */
    const EXP_STR_CONCAT = '$concat';
    const EXP_STR_STRCOMP = '$strcasecmp';
    const EXP_STR_SUBSTR = '$substr';
    const EXP_STR_LOWER = '$toLower';
    const EXP_STR_UPPER = '$toUpper';

    /**
     * String expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#text-search-expressions
     */
    const EXP_TEXT_SEARCH = '$meta';

    /**
     * Array expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#array-expressions
     */
    const EXP_ARRAY_SIZE = '$size';

    /**
     * Variable expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#variable-expressions
     */
    const EXP_VAR_BIND = '$let';
    const EXP_VAR_MAP = '$map';

    /**
     * Literal expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#literal-expressions
     */
    const EXP_LITERAL = '$literal';

    /**
     * Date expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#date-expressions
     */
    const EXP_DATE_DAY_WEEK = '$dayOfWeek';
    const EXP_DATE_DAY_YEAR = '$dayOfYear';
    const EXP_DATE_HOUR = '$hour';
    const EXP_DATE_MS = '$millisecond';
    const EXP_DATE_SEC = '$second';
    const EXP_DATE_MIN = '$minute';
    const EXP_DATE_DAY = '$dayOfMonth';
    const EXP_DATE_WEEK = '$week';
    const EXP_DATE_MONTH = '$month';
    const EXP_DATE_YEAR = '$year';

    /**
     * Conditional expressions
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#conditional-expressions
     */
    const EXP_COND = '$cond';
    const EXP_COND_IFNULL = '$ifNull';

    /**
     * Accumulators, available only for the $group stage
     * @link http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#accumulators
     */
    const GROUP_ADD = '$addToSet';
    const GROUP_AVG = '$avg';
    const GROUP_FIRST = '$first';
    const GROUP_LAST = '$last';
    const GROUP_MAX = '$max';
    const GROUP_MIN = '$min';
    const GROUP_PUSH = '$push';
    const GROUP_SUM = '$sum';

    /**
     * Collection where executing the query
     * @var Mongroove_Collection
     */
    protected $collection;

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
     * Retrieve collection
     *
     * @return Mongroove_Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Short method to create a new aggregation
     *
     * @param string $collection
     * @param Mongroove_Connection|null $connection
     * @return Mongroove_Operation
     * @throws Mongroove_Manager_Exception
     */
    public static function create($collection, $connection = null)
    {
        $manager = Mongroove_Manager::getInstance();
        $classname = function_exists('get_called_class') ? get_called_class() : Mongroove_Inflector::getCalledClass();
        $connection = is_null($connection) ? $manager->getCurrentConnection() : $manager->getConnection($connection);
        return new $classname($connection->getDatabase()->getCollection($collection));
    }
}