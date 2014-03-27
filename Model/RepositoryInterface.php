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
namespace mvrhov\Bundle\ResourceBundle\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Pagerfanta\Pagerfanta;

/**
 * Repository interface
 *
 * @author Miha Vrhovnik <miha.vrhovnik@cordia.si>
 */
interface RepositoryInterface extends ObjectRepository
{
    /**
     * Create empty model instance
     */
    public function create();

    /**
     * Persist instance
     *
     * @param object $instance
     * @param bool   $andFlush
     *
     * @return self
     */
    public function save($instance, $andFlush = true);

    /**
     * Delete instance
     *
     * @param object $instance
     * @param bool   $andFlush
     *
     * @return self
     */
    public function delete($instance, $andFlush = true);

    /**
     * Returns paginated collection
     *
     * @param array $criteria
     * @param array $orderBy
     *
     * @return Pagerfanta
     */
    public function findPaginated(array $criteria, array $orderBy = null);
}
