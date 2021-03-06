<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Route setup
 *
 * Route names are prepended with module name as: `<module>-<route>`
 *
 *
 * ```
 * array(
 *  // A specified route
 *  <route-key>   => array(
 *      // Optional route name
 *      'name'  => <route-name>
 *      // section: front, admin, feed, etc.
 *      'section'   => 'front',
 *      // order to call
 *      'priority'  => -999,
 *      // Type defined in `Pi\Mvc\Router\Route`
 *      'type'      => 'Standard',
 *      'options'   =>array(
 *          // Used as prefix,  default as module name; if no prefix, set it as '' or false explicitly
 *          'prefix' => '',
 *          'structure_delimiter'   => '/',
 *          'param_delimiter'       => '/',
 *          'key_value_delimiter'   => '-',
 *          'defaults'              => array(
 *              'module'        => 'system',
 *              'controller'    => 'public',
 *              'action'        => 'index',
 *          )
 *      )
 *  ),
 * );
 * ```
 *
 * - To use a route with specified name:
 * ```
 *  Pi::service('url')->assemble('<route-name>', array(<...>));
 * ```
 *
 * - To use a route with no specified name:
 * ```
 *  Pi::service('url')->assemble('<module>-<route-name>', array(<...>));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Route extends AbstractResource
{
    /**
     * Canonizes route config
     *
     * If route name is not specified, route name is generated
     * by module name and route key as: `<module>-<route-key>`
     *
     * @param array $configs
     * @return array
     */
    protected function canonize(array $configs)
    {
        $module    = $this->event->getParam('module');
        $directory = $this->event->getParam('directory');

        $routes = [];
        foreach ($configs as $key => $data) {
            if (isset($data['name'])) {
                $name = $data['name'];
                unset($data['name']);
            } else {
                $name = $key;
            }
            // Prepend module for routes of cloned modules
            if ($module != $directory) {
                $name = $module . '-' . $name;
            }
            $route = [
                'name'     => $name,
                'section'  => 'front',
                'module'   => $module,
                'priority' => 0,
            ];
            if (isset($data['section'])) {
                $route['section'] = $data['section'];
                unset($data['section']);
            }
            if (isset($data['priority'])) {
                $route['priority'] = $data['priority'];
                unset($data['priority']);
            }
            if (isset($data['options']['route'])) {
                $data['options']['prefix'] = $data['options']['route'];
                unset($data['options']['route']);
            }
            if (!isset($data['options']['prefix'])) {
                $data['options']['prefix'] = '/' . $module;
            }
            $data['options']['defaults']['module'] = $module;

            $route['data'] = $data;
            $routes[$name] = $route;
        }

        return $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }

        $modelRoute = Pi::model('route');
        $routes     = $this->canonize($this->config);

        // Add new routes
        foreach ($routes as $name => $data) {
            $row    = $modelRoute->createRow($data);
            $status = $row->save();
            if (!$status) {
                return [
                    'status'  => false,
                    'message' => sprintf('Route "%s" is not created.', $name),
                ];
            }
        }
        Pi::registry('route')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('route')->flush();
        if ($this->skipUpgrade()) {
            return;
        }

        $modelRoute = Pi::model('route');
        $routes     = $this->canonize($this->config);

        // Update existing routes
        $rowset = $modelRoute->select(['module' => $module]);
        foreach ($rowset as $row) {
            if (!isset($routes[$row->name])) {
                $row->delete();
                continue;
            }
            $row->assign($routes[$row->name]);
            $row->save();
            unset($routes[$row->name]);
        }

        // Add new routes
        foreach ($routes as $name => $data) {
            $row    = $modelRoute->createRow($data);
            $status = $row->save();
            if (!$status) {
                return [
                    'status'  => false,
                    'message' => sprintf('Route "%s" is not created.', $name),
                ];
            }
        }
        Pi::registry('route')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module     = $this->event->getParam('module');
        $modelRoute = Pi::model('route');
        $modelRoute->delete(['module' => $module]);
        Pi::registry('route')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module     = $this->event->getParam('module');
        $modelRoute = Pi::model('route');
        $modelRoute->update(['active' => 1], ['module' => $module]);
        Pi::registry('route')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module     = $this->event->getParam('module');
        $modelRoute = Pi::model('route');
        $modelRoute->update(['active' => 0], ['module' => $module]);
        Pi::registry('route')->flush();

        return true;
    }
}
