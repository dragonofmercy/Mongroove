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
class Mongroove_Utils_ReadPreference
{
    /**
     * Read preference types.
     *
     * The indexes correspond to the numeric values for each read preference
     * returned by getReadPreference() methods, while the values correspond to
     * the string constants expected by setReadPreference() methods.
     */
    private static $_types = array(
        MongoClient::RP_PRIMARY,
        MongoClient::RP_PRIMARY_PREFERRED,
        MongoClient::RP_SECONDARY,
        MongoClient::RP_SECONDARY_PREFERRED,
        MongoClient::RP_NEAREST
    );

    /**
     * Private constructor (prevents instantiation)
     */
    private function __construct()
    {
    }

    /**
     * Converts a numeric type returned by getReadPreference() methods to the
     * constant accepted by setReadPreference() methods.
     *
     * @param integer $type
     * @return string
     */
    public static function convertNumericType($type)
    {
        if(!isset(self::$_types[$type]))
        {
            throw new InvalidArgumentException('Unknown numeric read preference type: ' . $type);
        }

        return self::$_types[$type];
    }

    /**
     * Converts return values from getReadPreference() methods to the format
     * accepted by setReadPreference() methods.
     *
     * This is necessary for MongoClient, MongoDB, and MongoCollection classes
     * in driver versions between 1.3.0 and 1.3.3.
     *
     * @since 1.1
     * @param array $readPref
     * @return array
     */
    public static function convertReadPreference(array $readPref)
    {
        if(is_numeric($readPref['type']))
        {
            $readPref['type'] = self::convertNumericType($readPref['type']);
        }

        if(isset($readPref['type_string']))
        {
            unset($readPref['type_string']);
        }

        if(!empty($readPref['tagsets']))
        {
            $readPref['tagsets'] = self::convertTagSets($readPref['tagsets']);
        }

        return $readPref;
    }

    /**
     * Converts tag sets returned by getReadPreference() methods to the format
     * accepted by setReadPreference() methods.
     *
     * Example input:
     *
     *     [['dc:east', 'use:reporting'], ['dc:west'], []]
     *
     * Example output:
     *
     *     [['dc' => 'east', 'use' => 'reporting'], ['dc' => 'west'], []]
     *
     * @param array $tagSets
     * @return array
     */
    public static function convertTagSets(array $tagSets)
    {
        return array_map(function(array $tagSet){
            if(!isset($tagSet[0]) || false === strpos($tagSet[0], ':'))
            {
                return $tagSet;
            }

            $result = array();

            foreach($tagSet as $tagAndValue)
            {
                list($tag, $value) = explode(':', $tagAndValue, 2);
                $result[$tag] = $value;
            }

            return $result;
        }, $tagSets);
    }
}