<?php

namespace Sidalex\SwooleApp\Classes\Builder;

use HaydenPierce\ClassFinder\ClassFinder;
use Sidalex\SwooleApp\Classes\Controllers\ControllerInterface;
use Sidalex\SwooleApp\Classes\Wrapper\ConfigWrapper;

class RoutesCollectionBuilder
{
    /**
     * @return array<array> example [
     *      [
     * 'route_pattern_list' => ['','api','*','get_resume'], // /api/{all_string_write_in_parameters_fromURI}/get_resume
     * 'parameters_fromURI' => [2 =>'v1'],
     * 'method' => 'POST',
     * 'ControllerClass' => '{class_nameSpace}',
     *      ]
     * ]
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function buildRoutesCollection(ConfigWrapper $config): array
    {
        $classList = [];
        var_export($config->getConfigFromKey('controllers'));
        foreach ($config->getConfigFromKey('controllers') as $controller) {
            ClassFinder::disablePSR4Vendors(); // Optional; see performance notes below
            $classes = ClassFinder::getClassesInNamespace(
                $controller,
                ClassFinder::RECURSIVE_MODE
            );
            var_export($classes);
            $classList = array_merge($classList, $classes);
        }
        $repository = [];
        foreach ($classList as $class) {
            $reflection = new \ReflectionClass($class);
            $attributes = $reflection->getAttributes();
            $repositoryItem = [];
            $parameters_fromURIItem = [];
            if ($attributes[0]->getName() == 'Sidalex\\SwooleApp\\Classes\\Controllers\\Route') {
                $url_arr = explode('/', $attributes[0]->getArguments()['uri']);
                foreach ($url_arr as $number => $value) {
                    $itemUri = $value;
                    if ((str_starts_with($itemUri, '{')) && (str_ends_with($itemUri, '}'))) {
                        $itemUri = "*";
                        $parameters_fromURIItem[$number] = str_replace(['{', '}'], '', $value);
                    }
                    $repositoryItem['route_pattern_list'][$number] = $itemUri;
                }
                $repositoryItem['parameters_fromURI'] = $parameters_fromURIItem;
                $repositoryItem['method'] = $attributes[0]->getArguments()['method'];
                $repositoryItem['ControllerClass'] = $class;
            }
            $repository[] = $repositoryItem;
        }
        var_export($repository);
        return $repository;

    }

    public function searchInRoute(\Swoole\Http\Request $request, array $routesCollection)
    {
        $uri = explode("/", $request->server['request_uri']);
        return $this->findMatchingElement($routesCollection, $uri, $request->getMethod());

    }

    public function findMatchingElement($array1, $array2, $method,)
    {
        foreach ($array1 as $element) {
            $routePatternList = $element['route_pattern_list'];
            if (strtolower($method) != strtolower($element["method"])) {
                continue;
            }
            $match = true;
            for ($i = 0; $i < count($routePatternList); $i++) {
                if ($routePatternList[$i] !== '*' && $routePatternList[$i] !== $array2[$i]) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $element;
            }
        }
        return null;
    }

}