<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Navigation form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add(array(
            'name'          => 'name',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'Regex',
                    'options'   => array(
                        'pattern'   => '/[a-z0-9_]/',
                    ),
                ),
                new \Module\System\Validator\NavNameDuplicate(),
            ),
        ));

        $this->add(array(
            'name'          => 'title',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'section',
        ));

        /*
        $this->add(array(
            'name'          => 'active',
        ));
        */

        $this->add(array(
            'name'          => 'id',
            'required'      => false,
        ));

        $this->add(array(
            'name'          => 'parent',
            'required'      => false,
        ));
    }
}
