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
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      David Zeller <me@zellerda.com>
 * @license     http://www.opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       1.0
 */
class Mongroove_Query_Builder
{
    /**
     * The Mongroove_Collection instance.
     *
     * @var Mongroove_Collection
     */
    protected $collection;

    /**
     * Array containing the query data.
     *
     * @var array
     */
    protected $query = array('type' => Mongroove_Query::TYPE_FIND);

    /**
     * The Mongroove_Query_Expr instance used for building this query.
     *
     * @var Mongroove_Query_Expr $expr
     */
    protected $expr;

    /**
     * Create a new query builder.
     *
     * @param Mongroove_Collection $collection
     */
    public function __construct(Mongroove_Collection $collection)
    {
        $classname = $collection->getDatabase()->getConnection()->getManager()->getAttribute(Mongroove_Core::ATTR_CLASS_QUERY_EXPR);
        $this->collection = $collection;
        $this->expr = new $classname();
    }

    /**
     * Add an $and clause to the current query.
     *
     * You can create a new expression using the {@link Mongroove_Query_Builder::expr()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/and/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function addAnd($expression)
    {
        $this->expr->addAnd($expression);
        return $this;
    }

    /**
     * Add a $nor clause to the current query.
     *
     * You can create a new expression using the {@link Mongroove_Query_Builder::expr()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/nor/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function addNor($expression)
    {
        $this->expr->addNor($expression);
        return $this;
    }

    /**
     * Add an $or clause to the current query.
     *
     * You can create a new expression using the {@link Mongroove_Query_Builder::expr()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/or/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function addOr($expression)
    {
        $this->expr->addOr($expression);
        return $this;
    }

    /**
     * Append one or more values to the current array field only if they do not
     * already exist in the array.
     *
     * If the field does not exist, it will be set to an array containing the
     * unique value(s) in the argument. If the field is not an array, the query
     * will yield an error.
     *
     * Multiple values may be specified by provided an Mongroove_Query_Expr object and using
     * {@link Mongroove_Query_Expr::each()}.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/addToSet/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param mixed|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function addToSet($expression)
    {
        $this->expr->addToSet($expression);
        return $this;
    }

    /**
     * Specify $all criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/all/
     * @param array $values
     * @return Mongroove_Query_Builder
     */
    public function all(array $values)
    {
        $this->expr->all($values);
        return $this;
    }

    /**
     * Change the query type to count.
     *
     * @return Mongroove_Query_Builder
     */
    public function count()
    {
        $this->query['type'] = Mongroove_Query::TYPE_COUNT;
        return $this;
    }

    /**
     * Return an array of information about the Builder state for debugging.
     *
     * The $name parameter may be used to return a specific key from the
     * internal $query array property. If omitted, the entire array will be
     * returned.
     *
     * @param string $name
     * @return mixed
     */
    public function debug($name = null)
    {
        return $name !== null ? $this->query[$name] : $this->query;
    }

    /**
     * Set the "distanceMultiplier" option for a geoNear command query.
     *
     * @param float $distanceMultiplier
     * @return Mongroove_Query_Builder
     * @throws BadMethodCallException if the query is not a geoNear command
     */
    public function distanceMultiplier($distanceMultiplier)
    {
        if($this->query['type'] !== Mongroove_Query::TYPE_GEO_NEAR)
        {
            throw new BadMethodCallException('This method requires a geoNear command (call geoNear() first)');
        }

        $this->query['geoNear']['options']['distanceMultiplier'] = $distanceMultiplier;
        return $this;
    }

    /**
     * Change the query type to a distinct command.
     *
     * @see http://docs.mongodb.org/manual/reference/command/distinct/
     * @param string $field
     * @return Mongroove_Query_Builder
     */
    public function distinct($field)
    {
        $this->query['type'] = Mongroove_Query::TYPE_DISTINCT;
        $this->query['distinct'] = $field;
        return $this;
    }

    /**
     * Specify $elemMatch criteria for the current field.
     *
     * You can create a new expression using the {@link Mongroove_Query_Builder::expr()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/elemMatch/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function elemMatch($expression)
    {
        $this->expr->elemMatch($expression);
        return $this;
    }

    /**
     * Specify an equality match for the current field.
     *
     * @param mixed $value
     * @return Mongroove_Query_Builder
     */
    public function equals($value)
    {
        $this->expr->equals($value);
        return $this;
    }

    /**
     * Set one or more fields to be excluded from the query projection.
     *
     * If fields have been selected for inclusion, only the "_id" field may be
     * excluded.
     *
     * @param array|string $fieldName,...
     * @return Mongroove_Query_Builder
     */
    public function exclude($fieldName = null)
    {
        if(!isset($this->query['select']))
        {
            $this->query['select'] = array();
        }

        $fieldNames = is_array($fieldName) ? $fieldName : func_get_args();

        foreach($fieldNames as $fieldName)
        {
            $this->query['select'][$fieldName] = 0;
        }

        return $this;
    }

    /**
     * Specify $exists criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/exists/
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function exists($bool)
    {
        $this->expr->exists((boolean) $bool);
        return $this;
    }

    /**
     * Create a new Mongroove_Query_Expr instance that can be used to build partial expressions
     * for other operator methods.
     *
     * @return Mongroove_Query_Expr $expr
     */
    public function expr()
    {
        return new Mongroove_Query_Expr();
    }

    /**
     * Set the current field for building the expression.
     *
     * @param string $field
     * @return Mongroove_Query_Builder
     */
    public function field($field)
    {
        $this->expr->field((string) $field);
        return $this;
    }

    /**
     * Set the "finalize" option for a mapReduce or group command.
     *
     * @param string|MongoCode $finalize
     * @return Mongroove_Query_Builder
     * @throws BadMethodCallException if the query is not a mapReduce or group command
     */
    public function finalize($finalize)
    {
        switch($this->query['type'])
        {
            case Mongroove_Query::TYPE_MAP_REDUCE:
                $this->query['mapReduce']['options']['finalize'] = $finalize;
                break;

            case Mongroove_Query::TYPE_GROUP:
                $this->query['group']['options']['finalize'] = $finalize;
                break;

            default:
                throw new BadMethodCallException('mapReduce(), map() or group() must be called before finalize()');
        }

        return $this;
    }

    /**
     * Change the query type to find.
     *
     * @return Mongroove_Query_Builder
     */
    public function find()
    {
        $this->query['type'] = Mongroove_Query::TYPE_FIND;
        return $this;
    }

    /**
     * Change the query type to findAndRemove (uses the findAndModify command).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @return Mongroove_Query_Builder
     */
    public function findAndRemove()
    {
        $this->query['type'] = Mongroove_Query::TYPE_FIND_AND_REMOVE;
        return $this;
    }

    /**
     * Change the query type to findAndUpdate (uses the findAndModify command).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @return Mongroove_Query_Builder
     */
    public function findAndUpdate()
    {
        $this->query['type'] = Mongroove_Query::TYPE_FIND_AND_UPDATE;
        return $this;
    }

    /**
     * Add $geoIntersects criteria with a GeoJSON geometry to the query.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param array $geometry
     * @return Mongroove_Query_Builder
     */
    public function geoIntersects($geometry)
    {
        $this->expr->geoIntersects($geometry);
        return $this;
    }

    /**
     * Change the query type to a geoNear command.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation. If GeoJSON is
     * used, the "spherical" option will default to true.
     *
     * This method sets the "near" option for the geoNear command. The "num"
     * option may be set using {@link Mongroove_Query_Expr::limit()}. The "distanceMultiplier",
     * "maxDistance", "minDistance", and "spherical" options may be set using
     * their respective builder methods. Additional query criteria will be
     * assigned to the "query" option.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @param float|array $x
     * @param float $y
     * @return Mongroove_Query_Builder
     */
    public function geoNear($x, $y = null)
    {
        $this->query['type'] = Mongroove_Query::TYPE_GEO_NEAR;
        $this->query['geoNear'] = array('near' => is_array($x) ? $x : array($x, $y), 'options' => array('spherical' => is_array($x) && isset($x['type']),),);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a GeoJSON geometry to the query.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/geoWithin/
     * @param array $geometry
     * @return Mongroove_Query_Builder
     */
    public function geoWithin($geometry)
    {
        $this->expr->geoWithin($geometry);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $box shape to the query.
     *
     * A rectangular polygon will be constructed from a pair of coordinates
     * corresponding to the bottom left and top right corners.
     *
     * Note: the $box operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/box/
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return Mongroove_Query_Builder
     */
    public function geoWithinBox($x1, $y1, $x2, $y2)
    {
        $this->expr->geoWithinBox($x1, $y1, $x2, $y2);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $center shape to the query.
     *
     * Note: the $center operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/center/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return Mongroove_Query_Builder
     */
    public function geoWithinCenter($x, $y, $radius)
    {
        $this->expr->geoWithinCenter($x, $y, $radius);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $centerSphere shape to the query.
     *
     * Note: the $centerSphere operator supports both 2d and 2dsphere indexes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return Mongroove_Query_Builder
     */
    public function geoWithinCenterSphere($x, $y, $radius)
    {
        $this->expr->geoWithinCenterSphere($x, $y, $radius);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $polygon shape to the query.
     *
     * Point coordinates are in x, y order (easting, northing for projected
     * coordinates, longitude, latitude for geographic coordinates).
     *
     * The last point coordinate is implicitly connected with the first.
     *
     * Note: the $polygon operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/polygon/
     * @param array $point,... Three or more point coordinate tuples
     * @return Mongroove_Query_Builder
     */
    public function geoWithinPolygon(/* array($x1, $y1), ... */)
    {
        call_user_func_array(array($this->expr, 'geoWithinPolygon'), func_get_args());
        return $this;
    }

    /**
     * Return the expression's "new object".
     *
     * @see Mongroove_Query_Expr::getNewObj()
     * @return array
     */
    public function getNewObj()
    {
        return $this->expr->getNewObj();
    }

    /**
     * Set the expression's "new object".
     *
     * @param array $newObj
     * @return Mongroove_Query_Builder
     */
    public function setNewObj(array $newObj)
    {
        $this->expr->setNewObj($newObj);

        return $this;
    }

    /**
     * Create a new Query instance from the Builder state.
     *
     * @param array $options
     * @return Mongroove_Query
     */
    public function getQuery(array $options = array())
    {
        $query = $this->query;
        $query['query'] = $this->expr->getCriteria();
        $query['new_obj'] = $this->expr->getNewObj();

        return new Mongroove_Query($this->collection, $query, $options);
    }

    /**
     * Return the expression's query criteria.
     *
     * @return array
     */
    public function getCriteriaArray()
    {
        return $this->expr->getCriteria();
    }

    /**
     * Set the expression's query criteria.
     *
     * @see Mongroove_Query_Expr::setQuery()
     * @param array $criteria
     * @return Mongroove_Query_Builder
     */
    public function setQueryArray(array $criteria)
    {
        $this->expr->setCriteria($criteria);
        return $this;
    }

    /**
     * Get the type of this query.
     *
     * @return integer $type
     */
    public function getType()
    {
        return $this->query['type'];
    }

    /**
     * Change the query type to a group command.
     *
     * If the "reduce" option is not specified when calling this method, it must
     * be set with the {@link Mongroove_Query_Builder::reduce()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/command/group/
     * @param mixed $keys
     * @param array $initial
     * @param string|MongoCode $reduce
     * @param array $options
     * @return Mongroove_Query_Builder
     */
    public function group($keys, array $initial, $reduce = null, array $options = array())
    {
        $this->query['type'] = Mongroove_Query::TYPE_GROUP;
        $this->query['group'] = array('keys' => $keys, 'initial' => $initial, 'reduce' => $reduce, 'options' => $options);
        return $this;
    }

    /**
     * Specify $gt criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/gt/
     * @param mixed $value
     * @return Mongroove_Query_Builder
     */
    public function gt($value)
    {
        $this->expr->gt($value);
        return $this;
    }

    /**
     * Specify $gte criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/gte/
     * @param mixed $value
     * @return Mongroove_Query_Builder
     */
    public function gte($value)
    {
        $this->expr->gte($value);
        return $this;
    }

    /**
     * Set the index hint for the query.
     *
     * @param array|string $index
     * @return Mongroove_Query_Builder
     */
    public function hint($index)
    {
        $this->query['hint'] = $index;
        return $this;
    }

    /**
     * Set the immortal cursor flag.
     *
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function immortal($bool = true)
    {
        $this->query['immortal'] = (boolean) $bool;
        return $this;
    }

    /**
     * Specify $in criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/in/
     * @param array $values
     * @return Mongroove_Query_Builder
     */
    public function in(array $values)
    {
        $this->expr->in($values);
        return $this;
    }

    /**
     * Increment the current field.
     *
     * If the field does not exist, it will be set to this value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/inc/
     * @param float|integer $value
     * @return Mongroove_Query_Builder
     */
    public function inc($value)
    {
        $this->expr->inc($value);
        return $this;
    }

    /**
     * Change the query type to insert.
     *
     * @return Mongroove_Query_Builder
     */
    public function insert()
    {
        $this->query['type'] = Mongroove_Query::TYPE_INSERT;
        return $this;
    }

    /**
     * Set the $language option for $text criteria.
     *
     * This method must be called after text().
     *
     * @see http://docs.mongodb.org/manual/reference/operator/text/
     * @param string $language
     * @return Mongroove_Query_Builder
     */
    public function language($language)
    {
        $this->expr->language($language);
        return $this;
    }

    /**
     * Set the limit for the query.
     *
     * This is only relevant for find queries and geoNear and mapReduce
     * commands.
     *
     * @param integer $limit
     * @return Mongroove_Query_Builder
     */
    public function limit($limit)
    {
        $this->query['limit'] = (integer) $limit;
        return $this;
    }

    /**
     * Specify $lt criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return Mongroove_Query_Builder
     */
    public function lt($value)
    {
        $this->expr->lt($value);
        return $this;
    }

    /**
     * Specify $lte criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return Mongroove_Query_Builder
     */
    public function lte($value)
    {
        $this->expr->lte($value);
        return $this;
    }

    /**
     * Change the query type to a mapReduce command.
     *
     * The "reduce" option is not specified when calling this method; it must
     * be set with the {@link Mongroove_Query_Builder::reduce()} method.
     *
     * The "out" option defaults to inline, like {@link Mongroove_Query_Builder::mapReduce()}.
     *
     * @see http://docs.mongodb.org/manual/reference/command/mapReduce/
     * @param string|MongoCode $map
     * @return Mongroove_Query_Builder
     */
    public function map($map)
    {
        $this->query['type'] = Mongroove_Query::TYPE_MAP_REDUCE;
        $this->query['mapReduce'] = array('map' => $map, 'reduce' => null, 'out' => array('inline' => true), 'options' => array(),);
        return $this;
    }

    /**
     * Change the query type to a mapReduce command.
     *
     * @see http://docs.mongodb.org/manual/reference/command/mapReduce/
     * @param string|MongoCode $map
     * @param string|MongoCode $reduce
     * @param array|string $out
     * @param array $options
     * @return Mongroove_Query_Builder
     */
    public function mapReduce($map, $reduce, $out = array('inline' => true), array $options = array())
    {
        $this->query['type'] = Mongroove_Query::TYPE_MAP_REDUCE;
        $this->query['mapReduce'] = array('map' => $map, 'reduce' => $reduce, 'out' => $out, 'options' => $options);
        return $this;
    }

    /**
     * Set additional options for a mapReduce command.
     *
     * @param array $options
     * @return Mongroove_Query_Builder
     * @throws BadMethodCallException if the query is not a mapReduce command
     */
    public function mapReduceOptions(array $options)
    {
        if($this->query['type'] !== Mongroove_Query::TYPE_MAP_REDUCE)
        {
            throw new BadMethodCallException('This method requires a mapReduce command (call map() or mapReduce() first)');
        }

        $this->query['mapReduce']['options'] = $options;
        return $this;
    }

    /**
     * Set the "maxDistance" option for a geoNear command query or add
     * $maxDistance criteria to the query.
     *
     * If the query is a geoNear command ({@link Mongroove_Query_Expr::geoNear()} was called),
     * the "maxDistance" command option will be set; otherwise, $maxDistance
     * will be added to the current expression.
     *
     * If the query uses GeoJSON points, $maxDistance will be interpreted in
     * meters. If legacy point coordinates are used, $maxDistance will be
     * interpreted in radians.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @see http://docs.mongodb.org/manual/reference/operator/maxDistance/
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float $maxDistance
     * @return Mongroove_Query_Builder
     */
    public function maxDistance($maxDistance)
    {
        if($this->query['type'] === Mongroove_Query::TYPE_GEO_NEAR)
        {
            $this->query['geoNear']['options']['maxDistance'] = $maxDistance;
        }
        else
        {
            $this->expr->maxDistance($maxDistance);
        }

        return $this;
    }

    /**
     * Set the "minDistance" option for a geoNear command query or add
     * $minDistance criteria to the query.
     *
     * If the query is a geoNear command ({@link Mongroove_Query_Expr::geoNear()} was called),
     * the "minDistance" command option will be set; otherwise, $minDistance
     * will be added to the current expression.
     *
     * If the query uses GeoJSON points, $minDistance will be interpreted in
     * meters. If legacy point coordinates are used, $minDistance will be
     * interpreted in radians.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @see http://docs.mongodb.org/manual/reference/operator/minDistance/
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float $minDistance
     * @return Mongroove_Query_Builder
     */
    public function minDistance($minDistance)
    {
        if($this->query['type'] === Mongroove_Query::TYPE_GEO_NEAR)
        {
            $this->query['geoNear']['options']['minDistance'] = $minDistance;
        }
        else
        {
            $this->expr->minDistance($minDistance);
        }

        return $this;
    }

    /**
     * Specify $mod criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/mod/
     * @param float|integer $divisor
     * @param float|integer $remainder
     * @return Mongroove_Query_Builder
     */
    public function mod($divisor, $remainder = 0)
    {
        $this->expr->mod($divisor, $remainder);
        return $this;
    }

    /**
     * Set the "multiple" option for an update query.
     *
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function multiple($bool = true)
    {
        $this->query['multiple'] = (boolean) $bool;
        return $this;
    }

    /**
     * Add $near criteria to the query.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @param float|array $x
     * @param float $y
     * @return Mongroove_Query_Builder
     */
    public function near($x, $y = null)
    {
        $this->expr->near($x, $y);
        return $this;
    }

    /**
     * Add $nearSphere criteria to the query.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float|array $x
     * @param float $y
     * @return Mongroove_Query_Builder
     */
    public function nearSphere($x, $y = null)
    {
        $this->expr->nearSphere($x, $y);
        return $this;
    }

    /**
     * Negates an expression for the current field.
     *
     * You can create a new expression using the {@link Mongroove_Query_Builder::expr()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/not/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function not($expression)
    {
        $this->expr->not($expression);
        return $this;
    }

    /**
     * Specify $ne criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/ne/
     * @param mixed $value
     * @return Mongroove_Query_Builder
     */
    public function notEqual($value)
    {
        $this->expr->notEqual($value);
        return $this;
    }

    /**
     * Specify $nin criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/nin/
     * @param array $values
     * @return Mongroove_Query_Builder
     */
    public function notIn(array $values)
    {
        $this->expr->notIn($values);
        return $this;
    }

    /**
     * Set the "out" option for a mapReduce command.
     *
     * @param array|string $out
     * @return Mongroove_Query_Builder
     * @throws BadMethodCallException if the query is not a mapReduce command
     */
    public function out($out)
    {
        if($this->query['type'] !== Mongroove_Query::TYPE_MAP_REDUCE)
        {
            throw new BadMethodCallException('This method requires a mapReduce command (call map() or mapReduce() first)');
        }

        $this->query['mapReduce']['out'] = $out;
        return $this;
    }

    /**
     * Remove the first element from the current array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return Mongroove_Query_Builder
     */
    public function popFirst()
    {
        $this->expr->popFirst();
        return $this;
    }

    /**
     * Remove the last element from the current array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return Mongroove_Query_Builder
     */
    public function popLast()
    {
        $this->expr->popLast();
        return $this;
    }

    /**
     * Remove all elements matching the given value or expression from the
     * current array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pull/
     * @param mixed|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function pull($expression)
    {
        $this->expr->pull($expression);
        return $this;
    }

    /**
     * Remove all elements matching any of the given values from the current
     * array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pullAll/
     * @param array $values
     * @return Mongroove_Query_Builder
     */
    public function pullAll(array $values)
    {
        $this->expr->pullAll($values);
        return $this;
    }

    /**
     * Append one or more values to the current array field.
     *
     * If the field does not exist, it will be set to an array containing the
     * value(s) in the argument. If the field is not an array, the query
     * will yield an error.
     *
     * Multiple values may be specified by providing an Mongroove_Query_Expr object and using
     * {@link Mongroove_Query_Expr::each()}. {@link Mongroove_Query_Expr::slice()} and {@link Mongroove_Query_Expr::sort()} may
     * also be used to limit and order array elements, respectively.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/push/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @see http://docs.mongodb.org/manual/reference/operator/slice/
     * @see http://docs.mongodb.org/manual/reference/operator/sort/
     * @param mixed|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function push($expression)
    {
        $this->expr->push($expression);
        return $this;
    }

    /**
     * Specify $gte and $lt criteria for the current field.
     *
     * This method is shorthand for specifying $gte criteria on the lower bound
     * and $lt criteria on the upper bound. The upper bound is not inclusive.
     *
     * @param mixed $start
     * @param mixed $end
     * @return Mongroove_Query_Builder
     */
    public function range($start, $end)
    {
        $this->expr->range($start, $end);
        return $this;
    }

    /**
     * Set the "reduce" option for a mapReduce or group command.
     *
     * @param string|MongoCode $reduce
     * @return Mongroove_Query_Builder
     * @throws BadMethodCallException if the query is not a mapReduce or group command
     */
    public function reduce($reduce)
    {
        switch($this->query['type'])
        {
            case Mongroove_Query::TYPE_MAP_REDUCE:
                $this->query['mapReduce']['reduce'] = $reduce;
                break;

            case Mongroove_Query::TYPE_GROUP:
                $this->query['group']['reduce'] = $reduce;
                break;

            default:
                throw new BadMethodCallException('mapReduce(), map() or group() must be called before reduce()');
        }

        return $this;
    }

    /**
     * Change the query type to remove.
     *
     * @return Mongroove_Query_Builder
     */
    public function remove()
    {
        $this->query['type'] = Mongroove_Query::TYPE_REMOVE;
        return $this;
    }

    /**
     * Rename the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/rename/
     * @param string $name
     * @return Mongroove_Query_Builder
     */
    public function rename($name)
    {
        $this->expr->rename($name);
        return $this;
    }

    /**
     * Set the "new" option for a findAndUpdate command.
     *
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function returnNew($bool = true)
    {
        $this->query['new'] = (boolean) $bool;
        return $this;
    }

    /**
     * Set one or more fields to be included in the query projection.
     *
     * @param array|string $fieldName,...
     * @return Mongroove_Query_Builder
     */
    public function select($fieldName = null)
    {
        if(!isset($this->query['select']))
        {
            $this->query['select'] = array();
        }

        $fieldNames = is_array($fieldName) ? $fieldName : func_get_args();

        foreach($fieldNames as $fieldName)
        {
            $this->query['select'][$fieldName] = 1;
        }

        return $this;
    }

    /**
     * Select only matching embedded documents in an array field for the query
     * projection.
     *
     * @see http://docs.mongodb.org/manual/reference/projection/elemMatch/
     * @param string $fieldName
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Builder
     */
    public function selectElemMatch($fieldName, $expression)
    {
        if($expression instanceof Mongroove_Query_Expr)
        {
            $expression = $expression->getCriteria();
        }

        $this->query['select'][$fieldName] = array('$elemMatch' => $expression);
        return $this;
    }

    /**
     * Select a metadata field for the query projection.
     *
     * @see http://docs.mongodb.org/master/reference/operator/projection/meta/
     * @param string $fieldName
     * @param string $metaDataKeyword
     * @return Mongroove_Query_Builder
     */
    public function selectMeta($fieldName, $metaDataKeyword)
    {
        $this->query['select'][$fieldName] = array('$meta' => $metaDataKeyword);
        return $this;
    }

    /**
     * Select a slice of an array field for the query projection.
     *
     * The $countOrSkip parameter has two very different meanings, depending on
     * whether or not $limit is provided. See the MongoDB documentation for more
     * information.
     *
     * @see http://docs.mongodb.org/manual/reference/projection/slice/
     * @param string $fieldName
     * @param integer $countOrSkip Count parameter, or skip if limit is specified
     * @param integer $limit Limit parameter used in conjunction with skip
     * @return Mongroove_Query_Builder
     */
    public function selectSlice($fieldName, $countOrSkip, $limit = null)
    {
        $slice = $countOrSkip;

        if($limit !== null)
        {
            $slice = array($slice, $limit);
        }

        $this->query['select'][$fieldName] = array('$slice' => $slice);
        return $this;
    }

    /**
     * Set the current field to a value.
     *
     * This is only relevant for insert, update, or findAndUpdate queries. For
     * update and findAndUpdate queries, the $atomic parameter will determine
     * whether or not a $set operator is used.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/set/
     * @param mixed $value
     * @param boolean $atomic
     * @return Mongroove_Query_Builder
     */
    public function set($value, $atomic = true)
    {
        $this->expr->set($value, $atomic && $this->query['type'] !== Mongroove_Query::TYPE_INSERT);
        return $this;
    }

    /**
     * Set the read preference for the query.
     *
     * This is only relevant for read-only queries and commands.
     *
     * @see http://docs.mongodb.org/manual/core/read-preference/
     * @param mixed $readPreference
     * @param array $tags
     * @return Mongroove_Query_Builder
     */
    public function setReadPreference($readPreference, array $tags = array())
    {
        $this->query['readPreference'] = $readPreference;
        $this->query['readPreferenceTags'] = $tags;
        return $this;
    }

    /**
     * Specify $size criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/size/
     * @param integer $size
     * @return Mongroove_Query_Builder
     */
    public function size($size)
    {
        $this->expr->size((integer) $size);
        return $this;
    }

    /**
     * Set the skip for the query cursor.
     *
     * This is only relevant for find queries, or mapReduce queries that store
     * results in an output collecton and return a cursor.
     *
     * @param integer $skip
     * @return Mongroove_Query_Builder
     */
    public function skip($skip)
    {
        $this->query['skip'] = (integer) $skip;
        return $this;
    }

    /**
     * Set whether the query may be directed to replica set secondaries.
     *
     * If the driver supports read preferences and slaveOkay is true, a
     * "secondaryPreferred" read preference will be used. Otherwise, a "primary"
     * read preference will be used.
     *
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function slaveOkay($bool = true)
    {
        $this->query['slaveOkay'] = (boolean) $bool;
        return $this;
    }

    /**
     * Set the snapshot cursor flag.
     *
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function snapshot($bool = true)
    {
        $this->query['snapshot'] = (boolean) $bool;
        return $this;
    }

    /**
     * Set one or more field/order pairs on which to sort the query.
     *
     * If sorting by multiple fields, the first argument should be an array of
     * field name (key) and order (value) pairs.
     *
     * @param array|string $fieldName Field name or array of field/order pairs
     * @param int|string $order Field order (if one field is specified)
     * @return Mongroove_Query_Builder
     */
    public function sort($fieldName, $order = 1)
    {
        if(!isset($this->query['sort']))
        {
            $this->query['sort'] = array();
        }

        $fields = is_array($fieldName) ? $fieldName : array($fieldName => $order);

        foreach($fields as $fieldName => $order)
        {
            if(is_string($order))
            {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }

            $this->query['sort'][$fieldName] = (integer) $order;
        }

        return $this;
    }

    /**
     * Specify a projected metadata field on which to sort the query.
     *
     * Sort order is not configurable for metadata fields. Sorting by a metadata
     * field requires the same field and $meta expression to exist in the
     * projection document. This method will call {@link Mongroove_Query_Builder::selectMeta()}
     * if the field is not already set in the projection.
     *
     * @see http://docs.mongodb.org/master/reference/operator/projection/meta/#sort
     * @param string $fieldName Field name of the projected metadata
     * @param string $metaDataKeyword
     * @return Mongroove_Query_Builder
     */
    public function sortMeta($fieldName, $metaDataKeyword)
    {
        /* It's possible that the field is already projected without the $meta
         * operator. We'll assume that the user knows what they're doing in that
         * case and will not attempt to override the projection.
         */
        if(!isset($this->query['select'][$fieldName]))
        {
            $this->selectMeta($fieldName, $metaDataKeyword);
        }

        $this->query['sort'][$fieldName] = array('$meta' => $metaDataKeyword);
        return $this;
    }

    /**
     * Set the "spherical" option for a geoNear command query.
     *
     * @param bool $spherical
     * @return Mongroove_Query_Builder
     * @throws BadMethodCallException if the query is not a geoNear command
     */
    public function spherical($spherical = true)
    {
        if($this->query['type'] !== Mongroove_Query::TYPE_GEO_NEAR)
        {
            throw new BadMethodCallException('This method requires a geoNear command (call geoNear() first)');
        }

        $this->query['geoNear']['options']['spherical'] = $spherical;
        return $this;
    }

    /**
     * Specify $text criteria for the current field.
     *
     * The $language option may be set with {@link Mongroove_Query_Builder::language()}.
     *
     * @see http://docs.mongodb.org/master/reference/operator/query/text/
     * @param string $search
     * @return Mongroove_Query_Builder
     */
    public function text($search)
    {
        $this->expr->text($search);
        return $this;
    }

    /**
     * Specify $type criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/type/
     * @param integer $type
     * @return Mongroove_Query_Builder
     */
    public function type($type)
    {
        $this->expr->type($type);
        return $this;
    }

    /**
     * Unset the current field.
     *
     * The field will be removed from the document (not set to null).
     *
     * @see http://docs.mongodb.org/manual/reference/operator/unset/
     * @return Mongroove_Query_Builder
     */
    public function unsetField()
    {
        $this->expr->unsetField();
        return $this;
    }

    /**
     * Change the query type to update.
     *
     * @return Mongroove_Query_Builder
     */
    public function update()
    {
        $this->query['type'] = Mongroove_Query::TYPE_UPDATE;
        return $this;
    }

    /**
     * Set the "upsert" option for an update or findAndUpdate query.
     *
     * @param boolean $bool
     * @return Mongroove_Query_Builder
     */
    public function upsert($bool = true)
    {
        $this->query['upsert'] = (boolean) $bool;
        return $this;
    }

    /**
     * Specify a JavaScript expression to use for matching documents.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/where/
     * @param string|\MongoCode $javascript
     * @return Mongroove_Query_Builder
     */
    public function where($javascript)
    {
        $this->expr->where($javascript);
        return $this;
    }

    /**
     * @see http://php.net/manual/en/language.oop5.cloning.php
     */
    public function __clone()
    {
        $this->expr = clone $this->expr;
    }
}