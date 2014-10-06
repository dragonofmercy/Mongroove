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
class Mongroove_Configurable
{
    /**
     * Array of attributes
     * @var array
     */
    protected $attributes = array(
        Mongroove_Core::ATTR_MONGO_CLIENT_CONFIG => array(),
        Mongroove_Core::ATTR_CLASS_CONNECTION => 'Mongroove_Connection',
        Mongroove_Core::ATTR_CLASS_DOCUMENT => 'Mongroove_Document',
    );

    /**
     * Get attribute
     * If value not found return default value
     *
     * @param mixed $name Name of the attribute
     * @param null|mixed $default Default value if empty
     * @return null|mixed
     */
    public function getAttribute($name, $default = null)
    {
        if(isset($this->attributes[$name]))
        {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Set an attribute
     *
     * @param mixed $name Name of the attribute
     * @param mixed $value Value of the attribute
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
}