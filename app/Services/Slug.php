<?php

namespace App\Services;

use Stringy\StaticStringy;

class Slug
{
    protected $slug;
    protected $routeSlug;
    protected $routeName;
    protected $routeParameters;

    /**
     * Creates new instance.
     */
    public function __construct()
    {
        $this->setSlug(urldecode(\Request::path()));
    }

    /**
     * Get the Request slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug ?: '/';
    }

    /**
     * Set the Request slug
     *
     * @return void
     */
    public function setSlug($slug)
    {
        $this->slug = StaticStringy::removeLeft($slug, \Locales::getCurrent() . '/');
    }

    /**
     * Get the Request slugs
     *
     * @return array
     */
    public function getSlugs()
    {
        return $this->getSlug() == '/' ? [] : explode('/', $this->getSlug());
    }

    /**
     * Get the Route slug
     *
     * @return string
     */
    public function getRouteSlug()
    {
        return $this->routeSlug ?: '/';
    }

    /**
     * Get the Route name
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName ?: '/';
    }

    /**
     * Set the Route slug
     *
     * @return void
     */
    public function setRouteSlug($slug)
    {
        $slug = StaticStringy::removeLeft($slug, \Locales::getDomain()->domain . '/');
        $this->routeName = trim(StaticStringy::removeLeft($slug, \Locales::getCurrent() . '/'), '/');
        $this->routeSlug = $this->routeName;

        $parameters = \Locales::getRouteParameters($this->routeSlug);
        if (empty($parameters)) {
            $slugs = explode('/', $this->routeSlug);
            for ($i = count($slugs) - 1; $i >= 0; $i--) {
                $parameters = \Locales::getRouteParameters($slugs[$i]);
                if (!empty($parameters)) {
                    break;
                }
            }
        }

        foreach ($this->getRouteParameters() as $param) {
            $this->routeSlug .= '/' . array_search($param, $parameters);
        }

        $this->routeSlug = StaticStringy::removeRight($this->routeSlug, '/');
    }

    /**
     * Get the Route slugs
     *
     * @return array
     */
    public function getRouteSlugs()
    {
        return $this->getRouteSlug() == '/' ? [] : explode('/', $this->getRouteSlug());
    }

    /**
     * Get the Route parameters
     *
     * @return string
     */
    public function getRouteParameter()
    {
        return implode('/', array_values($this->getRouteParameters())); // str_replace($this->getRouteSlug(), '', $this->getSlug());
    }

    /**
     * Get the Route parameters
     *
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters ?: [];
    }

    /**
     * Set the Route parameters
     *
     * @return void
     */
    public function setRouteParameters($parameters)
    {
        $this->routeParameters = $parameters;
    }

    /**
     * Compares given route slug with current route slug
     *
     * @param  string  $slug
     * @param  int  $index
     * @return boolean
     */
    public function is($slug, $index = null)
    {
        $routeSlug = null;
        if ($index) {
            if (isset($this->getRouteSlugs()[--$index])) {
                $routeSlug = $this->getRouteSlugs()[$index];
            }
        } else {
            $routeSlug = rtrim($this->getRouteSlug(), '/');
        }

        return ($routeSlug == $slug || $slug == head(explode('/', $this->getSlug()))) ? true : false;
    }

    /**
     * Compares given route slug with current route slug
     *
     * @param  string  $slug
     * @param  int  $index
     * @return string Returns class name
     */
    public function isActive($slug, $index = null)
    {
        return $this->is($slug, $index) ? 'active' : null;
    }

    /**
     * Compares given route slug with current route slug
     *
     * @param  string  $slug
     * @param  int  $index
     * @return string Returns HTML class attribute
     */
    public function isActiveClass($slug, $index = null)
    {
        $class = $this->isActive($slug, $index);
        return $class ? ' class="' . $class . '"' : null;
    }

    public function arrayMatchSlugsRecursive($slugs, $array)
    {
        $slug = array_shift($slugs);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!isset($value['slug'])) {
                    $value['slug'] = $value['id'];
                }

                if ($value['slug'] == $slug) {
                    if (count($slugs)) {
                        if (isset($value['children'])) {
                            return $this->arrayMatchSlugsRecursive($slugs, $value['children']);
                        } else {
                            return false;
                        }
                    } else {
                        return $value;
                    }
                }
            }
        }

        return false;
    }

    public function createBreadcrumbsFromParameters($slugs, $array, $breadcrumbs = [])
    {
        $slug = array_shift($slugs);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!isset($value['slug'])) {
                    $value['slug'] = $value['id'];
                }

                if ($value['slug'] == $slug) {
                    $breadcrumbs[] = array_only($value, ['id', 'slug', 'name']);
                    if (count($slugs) && isset($value['children'])) {
                        return $this->createBreadcrumbsFromParameters($slugs, $value['children'], $breadcrumbs);
                    }
                }
            }
        }

        return $breadcrumbs;
    }
}
