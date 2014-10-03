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
class Mongroove_Inflector
{
    /**
     * Camelize a text.
     *
     * @param  string $text
     * @return string
     */
    public static function camelize($text)
    {
        return preg_replace_callback('~(_?)([-_])([\w])~', function($matches){ return $matches[1] . strtoupper($matches[3]); }, ucfirst(strtolower($text)));
    }

    /**
     * Returns the called class
     *
     * @return string
     */
    public static function getCalledClass()
    {
        $bt = debug_backtrace();
        $l = 0;

        do
        {
            $l++;
            $lines = file($bt[$l]['file']);
            $callerLine = $lines[$bt[$l]['line'] - 1];

            preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/', $callerLine, $matches);

            if($matches[1] == 'self')
            {
                $line = $bt[$l]['line'] - 1;

                while ($line > 0 && strpos($lines[$line], 'class') === false)
                {
                    $line--;
                }

                preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
            }
        }
        while ($matches[1] == 'parent' && $matches[1]);

        return $matches[1];
    }
}