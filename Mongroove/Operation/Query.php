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
class Mongroove_Operation_Query extends Mongroove_Operation
{
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
     * Add fields config to query
     *
     * @param array $fields
     * @return Mongroove_Operation_Query
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Add query parameter
     *
     * @param array $query
     * @return Mongroove_Operation_Query
     */
    public function query(array $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Querys this collection, returning a single element
     *
     * @return Mongroove_Document|boolean
     */
    public function findOne()
    {
        $data = $this->getCollection()->raw()->findOne($this->query, $this->fields);

        if(!empty($data))
        {
            return $this->getCollection()->getDocument()->fromArray($data);
        }
        else
        {
            return false;
        }
    }

    /**
     * Queries this collection, returning a Mongroove_Cursor for the result set
     *
     * @return Mongroove_Cursor
     */
    public function find()
    {
        return new Mongroove_Cursor($this->getCollection(), $this->getCollection()->raw()->find($this->query, $this->fields));
    }
}