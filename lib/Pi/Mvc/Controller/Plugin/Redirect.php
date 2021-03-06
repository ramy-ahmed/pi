<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Redirect as ZendRedirect;

/**
 * Action redirect plugin for controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Redirect extends ZendRedirect
{
    /** @var int Response code */
    protected $responseCode;

    /**
     * Generates a URL based on a route
     *
     * @param string $route RouteInterface name
     * @param array $params Parameters to use in url generation, if any
     * @param array $options RouteInterface-specific options to use in url generation, if any
     * @param bool $reuseMatchedParams
     *
     * @return Response|$this
     */
    public function __invoke(
        $route = null,
        array $params = [],
        $options = [],
        $reuseMatchedParams = false
    )
    {
        if (0 == func_num_args()) {
            return $this;
        }

        if (1 == func_num_args() && $route) {
            $result = $this->toUrl($route);
        } else {
            $result = $this->toRoute($route, $params, $options, $reuseMatchedParams);
        }

        return $result;
    }

    /**
     * Set response status code
     *
     * @param int $code
     * @return $this
     */
    public function setStatusCode($code)
    {
        $this->responseCode = $code;

        return $this;
    }

    /**
     * Generates a URL based on a route
     *
     * @param string $route RouteInterface name
     * @param array $params Parameters to use in url generation
     * @param array $options RouteInterface-specific options
     * @param bool $reuseMatchedParams
     *
     * @return Response
     */
    public function toRoute(
        $route = null,
        $params = [],
        $options = [],
        $reuseMatchedParams = false
    )
    {
        $this->controller->view()->setTemplate(false);

        $response = parent::toRoute(
            $route, $params, $options, $reuseMatchedParams
        );
        if ($this->responseCode) {
            $response->setStatusCode($this->responseCode);
            $this->responseCode = null;
        }
        $response->send();

        return $response;
    }

    /**
     * Redirect to the given URL
     *
     * @param string $url
     * @return Response
     */
    public function toUrl($url)
    {
        $this->controller->view()->setTemplate(false);
        $response = parent::toUrl($url);
        if ($this->responseCode) {
            $response->setStatusCode($this->responseCode);
            $this->responseCode = null;
        }
        $response->send();

        return $response;
    }
}
