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
class Mongroove_Collection
{
    /**
     * DB Object
     * @var Mongroove_Database
     */
    protected $db;

    /**
     * The document object
     * @var Mongroove_Document
     */
    protected $document;

    /**
     * Collection handler
     * @var MongoCollection
     */
    protected $raw;

    /**
     * Class constructor.
     *
     * @param Mongroove_Database $db
     * @param string $classname Document classname
     */
    public function __construct($db, $classname)
    {
        $this->db = $db;
        $document_name = $this->createDocument($classname);
        $this->raw = new MongoCollection($db->getDatabaseHandler(), $document_name);
    }

    /**
     * Retrieves database
     *
     * @return Mongroove_Database
     */
    public function getDatabase()
    {
        return $this->db;
    }

    /**
     * Retrieves collection handler
     *
     * @return MongoCollection
     */
    public function raw()
    {
        return $this->raw;
    }

    /**
     * Retrieves document
     *
     * @return Mongroove_Document
     */
    public function getDocument()
    {
        return clone $this->document;
    }

    /**
     * Create a new query
     *
     * @return Mongroove_Operation_Query
     */
    public function createQuery()
    {
        return new Mongroove_Operation_Query($this);
    }

    public function createAggregation()
    {
        return new Mongroove_Operation_Aggregate($this);
    }

    /**
     * Checks if document exists
     *
     * @param string $classname
     * @return string
     * @throws Mongroove_Document_Exception
     */
    protected function createDocument($classname)
    {
        if(!class_exists($classname, false))
        {
            if($this->getDatabase()->getConnection()->getManager()->getAttribute(Mongroove_Core::ATTR_FORCE_MODEL_USAGE))
            {
                throw new Mongroove_Collection_Exception('The document "' . $classname . '" is not declared in document repository');
            }
            else
            {
                $class = $this->getDatabase()->getConnection()->getManager()->getAttribute(Mongroove_Core::ATTR_CLASS_DOCUMENT);
                $this->document = new $class($this, $this->getDatabase()->getConnection());
                $this->document->updateDocumentName($classname);
            }
        }
        else
        {
            $this->document = new $classname($this, $this->getDatabase()->getConnection());
        }

        return $this->document->getDocumentName();
    }
}