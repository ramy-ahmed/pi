<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * Authentication and user service load
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Authentication extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        if (!empty($this->options['service'])) {
            foreach ($this->options['service'] as $key => $value) {
                Pi::service('authentication')->setOption($key, $value);
            }
        }

        try {
            Pi::service('authentication')->bind();
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }
    }
}
