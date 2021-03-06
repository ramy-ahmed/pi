<?PHP
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\Table;

use Pi\Application\Db;

/**
 *  Pi Master-Slave Table Gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MasterSlaveTableGateway extends AbstractTableGateway
{
    /**#@+
     * Master-Slave
     */
    /** @var Adapter Master adapter */
    protected $masterAdapter = null;

    /** @var Adapter Slave adapter */
    protected $slaveAdapter = null;
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        if ($this->initialized == true) {
            return;
        }

        if (!$this->masterAdapter && !$this->slaveAdapter) {
            throw new \Exception(
                'Master/Slave adapters must be configured in initialize()'
            );
        }
        $this->adapter = $this->adapter ?: $this->slaveAdapter;

        parent::initialize();
    }

    /**
     * Get adapter
     *
     * @param string $type
     * @return Adapter
     */
    public function getAdapter($type = null)
    {
        if ('master' == $type) {
            return $this->masterAdapter;
        } elseif ('slave' == $type) {
            return $this->slaveAdapter;
        } else {
            return $this->adapter;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function select($where = null)
    {
        $this->adapter = $this->slaveAdapter;

        return parent::select($where);
    }

    /**
     * {@inheritDoc}
     */
    public function insert($set)
    {
        $this->adapter = $this->masterAdapter;

        return parent::insert($set);
    }

    /**
     * {@inheritDoc}
     */
    public function update($set, $where = null)
    {
        $this->adapter = $this->masterAdapter;

        return parent::update($set, $where);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($where)
    {
        $this->adapter = $this->masterAdapter;

        return parent::delete($where);
    }
}
