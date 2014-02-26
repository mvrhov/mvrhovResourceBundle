<?php
/**
 * Released under the MIT License.
 *
 * Copyright (c) 2012 - 2014 Miha Vrhovnik <miha.vrhovnik@cordia.si>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace mvrhov\Bundle\ResourceBundle\Doctrine;

/**
 * Criteria parameter comparison
 *
 * @author Miha Vrhovnik <miha.vrhovnik@cordia.si>
 */
class Comparison
{
    const EQ        = '=';
    const NEQ       = '<>';
    const LT        = '<';
    const LTE       = '<=';
    const GT        = '>';
    const GTE       = '>=';
    const IS        = '='; // no difference with EQ
    const IN        = 'IN';
    const NOTIN     = 'NOT IN';
    const CONTAINS  = 'CONTAINS';

    /**
     * Verifies if parameter is a comparison operator
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isComparison($value)
    {
        $cc = get_called_class();

        if ($value instanceof $cc) {
            return true;
        }

        $r = new \ReflectionClass($cc);

        return in_array($value, $r->getConstants());
    }

    /**
     * Breaks a value to two parts comparison operator and a value itself
     *
     * @param $value
     *
     * @return array(operator => value)
     */
    public static function breakValue($value)
    {
        if (preg_match_all('/^(<=|>=|<>|<|=|>)(.*)/', $value, $matches)) {
            return array($matches[1][0] => $matches[2][0]);
        }

        return $value;
    }
}
