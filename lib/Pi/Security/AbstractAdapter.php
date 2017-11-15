<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Security;

/**
 * Abstract security adapter class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter
{
    /** @var string Prompt message */
    const MESSAGE = 'DEFINE SPECIFIC MESSAGE';

    /**
     * Check against security settings
     *
     * Policy with different result:
     *
     * - true: following evaluations will be terminated and current request
     *      is approved
     * - false: following evaluations will be terminated and current request
     *      is denied
     * - null: continue
     *
     * @see http://www.php.net/manual/en/migration52.incompatible.php
     *      for "Dropped abstract static class functions"
     *
     * @param array $options
     *
     * @throws \Exception
     * @return bool|null
     */
    public static function check($options = null)
    {
        throw new \Exception('The method is not implemented.');
    }
}
