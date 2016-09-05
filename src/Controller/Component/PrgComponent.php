<?php
namespace Cake\Controller\Component;

use Cake\Controller\Component;

class PrgComponent extends Component
{
    /**
     * Default config for the Prg Component.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'allowedFilters' => []
    ];

    public function redirectPost()
    {
        $controller = $this->_registry->getController();
        if ($controller->request->is('post')) {
            list($data, $query) = $this->cleanParams($controller->request->data, [
                'rinse' => false,
            ]);
            return $controller->redirect(array('?' => $data, 'escape' => false));
        }
    }

    /**
     * Clean Parameters
     *
     * @param array $named
     * @param array $options
     * @return array
     */
    public function cleanParams($named, $options = [])
    {
        if (empty($named)) {
            return [[], ''];
        }

        $coalesce = '';
        if (is_bool($options)) {
            $options = ['rinse' => $options];
        }

        $options = array_merge([
            'allowedFilters' => $this->config('allowedFilters'),
            'coalesce' => false,
            'rinse' => [
                'search' => ' ',
                'replace' => ' ',
            ],
            'trim' => " \t\n\r\0\x0B+\"",
        ], $options);

        if ($options['rinse'] === true) {
            $options['rinse'] = [
                'search' => '+',
                'replace' => ' ',
            ];
        }

        if (!empty($options['allowedFilters'])) {
            $named = array_intersect_key($named, array_combine($options['allowedFilters'], $options['allowedFilters']));
        }

        if (isset($named['query']) && is_string($named['query']) && strlen($named['query'])) {
            $named['query'] = str_replace('\'', '"', $named['query']);
            preg_match_all('/\s*(\w+):\s*("[^"]*"|[^"\s]+)/', $named['query'], $matches, PREG_SET_ORDER);

            $query = preg_replace('/\s*(\w+):\s*("[^"]*"|[^"\s]+)/', '', $named['query']);
            if ($query === null) {
                $query = '';
            }

            $query = ' ' . trim($query, $options['trim']);
            foreach ($matches as $value) {
                $key = strtolower($value[1]);
                if (!in_array($key, $options['allowedFilters'])) {
                    $query .= ' ' . $key . ':' . $value[2];
                    continue;
                }

                if (isset($named[$key]) && $key == 'has') {
                    if (is_array($named[$key])) {
                        $named[$key][] = trim($value[2], $options['trim']);
                    } elseif (isset($named[$key])) {
                        $named[$key] = [
                            $named[$key],
                            trim($value[2], $options['trim'])
                        ];
                    }
                } else {
                    $named[$key] = trim($value[2], $options['trim']);
                }
            }

            $named['query'] = trim($query, $options['trim']);
        }

        foreach ($named as $key => $value) {
            if (is_array($value)) {
                $values = [];
                foreach ($value as $v) {
                    $values[] = str_replace(
                        $options['rinse']['search'],
                        $options['rinse']['replace'],
                        $v
                    );
                }
                $named[$key] = $values;
            } else {
                $named[$key] = str_replace(
                    $options['rinse']['search'],
                    $options['rinse']['replace'],
                    $value
                );
            }
        }

        if ($options['coalesce']) {
            foreach ($named as $key => $value) {
                if ($key == 'query') {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $v) {
                        if (strstr($v, ' ') !== false) {
                            $coalesce .= " {$key}:\"{$v}\"";
                        } else {
                            $coalesce .= " {$key}:{$v}";
                        }
                    }
                } else {
                    if (strstr($value, ' ') !== false) {
                        $coalesce .= " {$key}:\"{$value}\"";
                    } else {
                        $coalesce .= " {$key}:{$value}";
                    }
                }
            }

            $coalesce = trim($coalesce, $options['trim']);
            if (isset($named['query'])) {
                $coalesce = trim($named['query'], $options['trim']) . ' ' . $coalesce;
            }
        }

        $clean = [];
        foreach ($named as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $value;
            }

            if (is_string($value) && strlen($value)) {
                $clean[$key] = $value;
            }
        }
        $named = $clean;

        return [$named, trim($coalesce)];
    }

}
