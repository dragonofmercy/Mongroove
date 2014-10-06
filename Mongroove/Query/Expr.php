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
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      David Zeller <me@zellerda.com>
 * @license     http://www.opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       1.0
 */
class Mongroove_Query_Expr
{
    /**
     * Array containing criteria options
     *
     * @var array
     */
    protected $criteria = array();

    /**
     * Current field
     *
     * @var string
     */
    protected $current_field;

    /**
     * The "new object" array containing either a full document or a number of
     * atomic update operators.
     *
     * @see docs.mongodb.org/manual/reference/method/db.collection.update/#update-parameter
     * @var array
     */
    protected $new_obj = array();

    /**
     * Retrieve the criteria
     *
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Add an $and clause to the current query.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/and/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Expr
     */
    public function addAnd($expression)
    {
        $this->criteria['$and'][] = $expression instanceof Mongroove_Query_Expr ? $expression->getCriteria() : $expression;
        return $this;
    }

    /**
     * Add a $nor clause to the current query.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/nor/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Expr
     */
    public function addNor($expression)
    {
        $this->criteria['$nor'][] = $expression instanceof Mongroove_Query_Expr ? $expression->getCriteria() : $expression;
        return $this;
    }

    /**
     * Add an $or clause to the current query.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/or/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Expr
     */
    public function addOr($expression)
    {
        $this->criteria['$or'][] = $expression instanceof Mongroove_Query_Expr ? $expression->getCriteria() : $expression;
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
     * {@link Expr::each()}.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/addToSet/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param mixed|Mongroove_Query_Expr $valueOrExpression
     * @return Mongroove_Query_Expr
     */
    public function addToSet($valueOrExpression)
    {
        if($valueOrExpression instanceof Mongroove_Query_Expr)
        {
            $valueOrExpression = $valueOrExpression->getCriteria();
        }

        $this->requiresCurrentField();
        $this->new_obj['$addToSet'][$this->current_field] = $valueOrExpression;
        return $this;
    }

    /**
     * Specify $all criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/all/
     * @param array $values
     * @return Mongroove_Query_Expr
     */
    public function all(array $values)
    {
        return $this->operator('$all', (array) $values);
    }

    /**
     * Add $each criteria to the expression for a $push operation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param array $values
     * @return Mongroove_Query_Expr
     */
    public function each(array $values)
    {
        return $this->operator('$each', $values);
    }

    /**
     * Specify $elemMatch criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/elemMatch/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Expr
     */
    public function elemMatch($expression)
    {
        return $this->operator('$elemMatch', $expression instanceof Mongroove_Query_Expr ? $expression->getCriteria() : $expression);
    }

    /**
     * Specify an equality match for the current field.
     *
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function equals($value)
    {
        if($this->current_field)
        {
            $this->criteria[$this->current_field] = $value;
        }
        else
        {
            $this->criteria = $value;
        }

        return $this;
    }

    /**
     * Specify $exists criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/exists/
     * @param boolean $bool
     * @return Mongroove_Query_Expr
     */
    public function exists($bool)
    {
        return $this->operator('$exists', (boolean) $bool);
    }

    /**
     * Set the current field for building the expression.
     *
     * @param string $field
     * @return Mongroove_Query_Expr
     */
    public function field($field)
    {
        $this->current_field = (string) $field;
        return $this;
    }

    /**
     * Add $geoIntersects criteria with a GeoJSON geometry to the expression.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param array
     * @return Mongroove_Query_Expr
     */
    public function geoIntersects($geometry)
    {
        return $this->operator('$geoIntersects', array('$geometry' => $geometry));
    }

    /**
     * Add $geoWithin criteria with a GeoJSON geometry to the expression.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param array
     * @return Mongroove_Query_Expr
     */
    public function geoWithin($geometry)
    {
        return $this->operator('$geoWithin', array('$geometry' => $geometry));
    }

    /**
     * Add $geoWithin criteria with a $box shape to the expression.
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
     * @return Mongroove_Query_Expr
     */
    public function geoWithinBox($x1, $y1, $x2, $y2)
    {
        $shape = array('$box' => array(array($x1, $y1), array($x2, $y2)));
        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $center shape to the expression.
     *
     * Note: the $center operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/center/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return Mongroove_Query_Expr
     */
    public function geoWithinCenter($x, $y, $radius)
    {
        $shape = array('$center' => array(array($x, $y), $radius));
        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $centerSphere shape to the expression.
     *
     * Note: the $centerSphere operator supports both 2d and 2dsphere indexes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return Mongroove_Query_Expr
     */
    public function geoWithinCenterSphere($x, $y, $radius)
    {
        $shape = array('$centerSphere' => array(array($x, $y), $radius));
        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $polygon shape to the expression.
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
     * @return Mongroove_Query_Expr
     * @throws InvalidArgumentException if less than three points are given
     */
    public function geoWithinPolygon(/* array($x1, $y1), ... */)
    {
        if(func_num_args() < 3)
        {
            throw new InvalidArgumentException('Polygon must be defined by three or more points.');
        }

        $shape = array('$polygon' => func_get_args());

        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Return the current field.
     *
     * @return string
     */
    public function getCurrentField()
    {
        return $this->current_field;
    }

    /**
     * Return the "new object".
     *
     * @return array
     */
    public function getNewObj()
    {
        return $this->new_obj;
    }

    /**
     * Set the "new object".
     *
     * @param array $new_obj
     * @return Mongroove_Query_Expr
     */
    public function setNewObj(array $new_obj)
    {
        $this->new_obj = $new_obj;
    }

    /**
     * Set the query criteria.
     *
     * @param array $criteria
     * @return Mongroove_Query_Expr
     */
    public function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Specify $gt criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/gt/
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function gt($value)
    {
        return $this->operator('$gt', $value);
    }

    /**
     * Specify $gte criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/gte/
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function gte($value)
    {
        return $this->operator('$gte', $value);
    }

    /**
     * Specify $in criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/in/
     * @param array $values
     * @return Mongroove_Query_Expr
     */
    public function in(array $values)
    {
        return $this->operator('$in', array_values($values));
    }

    /**
     * Increment the current field.
     *
     * If the field does not exist, it will be set to this value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/inc/
     * @param float|integer $value
     * @return Mongroove_Query_Expr
     */
    public function inc($value)
    {
        $this->requiresCurrentField();
        $this->new_obj['$inc'][$this->current_field] = $value;

        return $this;
    }

    /**
     * Set the $language option for $text criteria.
     *
     * This method must be called after text().
     *
     * @see http://docs.mongodb.org/manual/reference/operator/text/
     * @param string $language
     * @return Mongroove_Query_Expr
     * @throws BadMethodCallException if the query does not already have $text criteria
     */
    public function language($language)
    {
        if(!isset($this->criteria['$text']))
        {
            throw new BadMethodCallException('This method requires a $text operator (call text() first)');
        }

        $this->criteria['$text']['$language'] = (string) $language;

        return $this;
    }

    /**
     * Specify $lt criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function lt($value)
    {
        return $this->operator('$lt', $value);
    }

    /**
     * Specify $lte criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function lte($value)
    {
        return $this->operator('$lte', $value);
    }

    /**
     * Set the $maxDistance option for $near or $nearSphere criteria.
     *
     * This method must be called after near() or nearSphere(), since placement
     * of the $maxDistance option depends on whether a GeoJSON point or legacy
     * coordinates were provided for $near/$nearSphere.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/maxDistance/
     * @param float $maxDistance
     * @return Mongroove_Query_Expr
     * @throws BadMethodCallException if the query does not already have $near or $nearSphere criteria
     */
    public function maxDistance($maxDistance)
    {
        if($this->current_field)
        {
            $query = &$this->criteria[$this->current_field];
        }
        else
        {
            $query = &$this->criteria;
        }

        if(!isset($query['$near']) && !isset($query['$nearSphere']))
        {
            throw new BadMethodCallException('This method requires a $near or $nearSphere operator (call near() or nearSphere() first)');
        }

        if(isset($query['$near']['$geometry']))
        {
            $query['$near']['$maxDistance'] = $maxDistance;
        }
        elseif(isset($query['$nearSphere']['$geometry']))
        {
            $query['$nearSphere']['$maxDistance'] = $maxDistance;
        }
        else
        {
            $query['$maxDistance'] = $maxDistance;
        }

        return $this;
    }

    /**
     * Set the $minDistance option for $near or $nearSphere criteria.
     *
     * This method must be called after near() or nearSphere(), since placement
     * of the $minDistance option depends on whether a GeoJSON point or legacy
     * coordinates were provided for $near/$nearSphere.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/minDistance/
     * @param float $minDistance
     * @return Mongroove_Query_Expr
     * @throws BadMethodCallException if the query does not already have $near or $nearSphere criteria
     */
    public function minDistance($minDistance)
    {
        if($this->current_field)
        {
            $query = &$this->criteria[$this->current_field];
        }
        else
        {
            $query = &$this->criteria;
        }

        if(!isset($query['$near']) && !isset($query['$nearSphere']))
        {
            throw new BadMethodCallException('This method requires a $near or $nearSphere operator (call near() or nearSphere() first)');
        }

        if(isset($query['$near']['$geometry']))
        {
            $query['$near']['$minDistance'] = $minDistance;
        }
        elseif(isset($query['$nearSphere']['$geometry']))
        {
            $query['$nearSphere']['$minDistance'] = $minDistance;
        }
        else
        {
            $query['$minDistance'] = $minDistance;
        }

        return $this;
    }

    /**
     * Specify $mod criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/mod/
     * @param float|integer $divisor
     * @param float|integer $remainder
     * @return Mongroove_Query_Expr
     */
    public function mod($divisor, $remainder = 0)
    {
        return $this->operator('$mod', array($divisor, $remainder));
    }

    /**
     * Add $near criteria to the expression.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @param float|array $x
     * @param float $y
     * @return Mongroove_Query_Expr
     */
    public function near($x, $y = null)
    {
        if(is_array($x))
        {
            return $this->operator('$near', array('$geometry' => $x));
        }

        return $this->operator('$near', array($x, $y));
    }

    /**
     * Add $nearSphere criteria to the expression.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float|array $x
     * @param float $y
     * @return Mongroove_Query_Expr
     */
    public function nearSphere($x, $y = null)
    {
        if(is_array($x))
        {
            return $this->operator('$nearSphere', array('$geometry' => $x));
        }

        return $this->operator('$nearSphere', array($x, $y));
    }

    /**
     * Negates an expression for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/not/
     * @param array|Mongroove_Query_Expr $expression
     * @return Mongroove_Query_Expr
     */
    public function not($expression)
    {
        return $this->operator('$not', $expression instanceof Mongroove_Query_Expr ? $expression->getCriteria() : $expression);
    }

    /**
     * Specify $ne criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/ne/
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function notEqual($value)
    {
        return $this->operator('$ne', $value);
    }

    /**
     * Specify $nin criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/nin/
     * @param array $values
     * @return Mongroove_Query_Expr
     */
    public function notIn(array $values)
    {
        return $this->operator('$nin', array_values($values));
    }

    /**
     * Defines an operator and value on the expression.
     *
     * If there is a current field, the operator will be set on it; otherwise,
     * the operator is set at the top level of the query.
     *
     * @param string $operator
     * @param mixed $value
     * @return Mongroove_Query_Expr
     */
    public function operator($operator, $value)
    {
        if($this->current_field)
        {
            $this->criteria[$this->current_field][$operator] = $value;
        }
        else
        {
            $this->criteria[$operator] = $value;
        }

        return $this;
    }

    /**
     * Remove the first element from the current array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return Mongroove_Query_Expr
     */
    public function popFirst()
    {
        $this->requiresCurrentField();
        $this->new_obj['$pop'][$this->current_field] = 1;
        return $this;
    }

    /**
     * Remove the last element from the current array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return Mongroove_Query_Expr
     */
    public function popLast()
    {
        $this->requiresCurrentField();
        $this->new_obj['$pop'][$this->current_field] = -1;
        return $this;
    }

    /**
     * Remove all elements matching the given value or expression from the
     * current array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pull/
     * @param mixed|Mongroove_Query_Expr $valueOrExpression
     * @return Mongroove_Query_Expr
     */
    public function pull($valueOrExpression)
    {
        if($valueOrExpression instanceof Mongroove_Query_Expr)
        {
            $valueOrExpression = $valueOrExpression->getCriteria();
        }

        $this->requiresCurrentField();
        $this->new_obj['$pull'][$this->current_field] = $valueOrExpression;
        return $this;
    }

    /**
     * Remove all elements matching any of the given values from the current
     * array field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/pullAll/
     * @param array $values
     * @return Mongroove_Query_Expr
     */
    public function pullAll(array $values)
    {
        $this->requiresCurrentField();
        $this->new_obj['$pullAll'][$this->current_field] = $values;
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
     * {@link Expr::each()}. {@link Expr::slice()} and {@link Expr::sort()} may
     * also be used to limit and order array elements, respectively.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/push/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @see http://docs.mongodb.org/manual/reference/operator/slice/
     * @see http://docs.mongodb.org/manual/reference/operator/sort/
     * @param mixed|Mongroove_Query_Expr $valueOrExpression
     * @return Mongroove_Query_Expr
     */
    public function push($valueOrExpression)
    {
        if($valueOrExpression instanceof Mongroove_Query_Expr)
        {
            $valueOrExpression = array_merge(array('$each' => array()), $valueOrExpression->getCriteria());
        }

        $this->requiresCurrentField();
        $this->new_obj['$push'][$this->current_field] = $valueOrExpression;
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
     * @return Mongroove_Query_Expr
     */
    public function range($start, $end)
    {
        return $this->operator('$gte', $start)->operator('$lt', $end);
    }

    /**
     * Rename the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/rename/
     * @param string $name
     * @return Mongroove_Query_Expr
     */
    public function rename($name)
    {
        $this->requiresCurrentField();
        $this->new_obj['$rename'][$this->current_field] = $name;

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
     * @return Mongroove_Query_Expr
     */
    public function set($value, $atomic = true)
    {
        $this->requiresCurrentField();

        if($atomic)
        {
            $this->new_obj['$set'][$this->current_field] = $value;

            return $this;
        }

        if(strpos($this->current_field, '.') === false)
        {
            $this->new_obj[$this->current_field] = $value;

            return $this;
        }

        $keys = explode('.', $this->current_field);
        $current = &$this->new_obj;

        foreach($keys as $key)
        {
            $current = &$current[$key];
        }

        $current = $value;
        return $this;
    }

    /**
     * Specify $size criteria for the current field.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/size/
     * @param integer $size
     * @return Mongroove_Query_Expr
     */
    public function size($size)
    {
        return $this->operator('$size', (integer) $size);
    }

    /**
     * Add $slice criteria to the expression for a $push operation.
     *
     * This is useful in conjunction with {@link Expr::each()} for a
     * {@link Expr::push()} operation. {@link Builder::selectSlice()} should be
     * used for specifying $slice for a query projection.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/slice/
     * @param integer $slice
     * @return Mongroove_Query_Expr
     */
    public function slice($slice)
    {
        return $this->operator('$slice', $slice);
    }

    /**
     * Add $sort criteria to the expression for a $push operation.
     *
     * If sorting by multiple fields, the first argument should be an array of
     * field name (key) and order (value) pairs.
     *
     * This is useful in conjunction with {@link Expr::each()} for a
     * {@link Expr::push()} operation. {@link Builder::sort()} should be used to
     * sort the results of a query.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/sort/
     * @param array|string $fieldName Field name or array of field/order pairs
     * @param int|string $order Field order (if one field is specified)
     * @return Mongroove_Query_Expr
     */
    public function sort($fieldName, $order = null)
    {
        $fields = is_array($fieldName) ? $fieldName : array($fieldName => $order);
        $sort = array();

        foreach($fields as $fieldName => $order)
        {
            if(is_string($order))
            {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }

            $sort[$fieldName] = (integer) $order;
        }

        return $this->operator('$sort', $sort);
    }

    /**
     * Specify $text criteria for the current query.
     *
     * The $language option may be set with {@link Expr::language()}.
     *
     * @see http://docs.mongodb.org/master/reference/operator/query/text/
     * @param string $search
     * @return Mongroove_Query_Expr
     */
    public function text($search)
    {
        $this->criteria['$text'] = array('$search' => (string) $search);
        return $this;
    }

    /**
     * Specify $type criteria for the current field.
     *
     * @todo Remove support for string $type argument in 2.0
     * @see http://docs.mongodb.org/manual/reference/operator/type/
     * @param integer $type
     * @return Mongroove_Query_Expr
     */
    public function type($type)
    {
        if(is_string($type))
        {
            $map = array(
                'double' => 1,
                'string' => 2,
                'object' => 3,
                'array' => 4,
                'binary' => 5,
                'undefined' => 6,
                'objectid' => 7,
                'boolean' => 8,
                'date' => 9,
                'null' => 10,
                'regex' => 11,
                'jscode' => 13,
                'symbol' => 14,
                'jscodewithscope' => 15,
                'integer32' => 16,
                'timestamp' => 17,
                'integer64' => 18,
                'maxkey' => 127,
                'minkey' => 255
            );

            $type = isset($map[$type]) ? $map[$type] : $type;
        }

        return $this->operator('$type', $type);
    }

    /**
     * Unset the current field.
     *
     * The field will be removed from the document (not set to null).
     *
     * @see http://docs.mongodb.org/manual/reference/operator/unset/
     * @return Mongroove_Query_Expr
     */
    public function unsetField()
    {
        $this->requiresCurrentField();
        $this->new_obj['$unset'][$this->current_field] = 1;
        return $this;
    }

    /**
     * Specify a JavaScript expression to use for matching documents.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/where/
     * @param string|\MongoCode $javascript
     * @return Mongroove_Query_Expr
     */
    public function where($javascript)
    {
        $this->criteria['$where'] = $javascript;
        return $this;
    }

    /**
     * Ensure that a current field has been set.
     *
     * @throws LogicException if a current field has not been set
     */
    private function requiresCurrentField()
    {
        if(!$this->current_field)
        {
            throw new LogicException('This method requires you set a current field using field().');
        }
    }
}