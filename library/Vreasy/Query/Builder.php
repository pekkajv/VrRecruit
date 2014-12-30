<?php
namespace Vreasy\Query;

class Builder
{
    public static function expandWhere(
        $params,
        $options = ['wildcard' => false, 'prefix' => '']
        ) {

        $or = 'or'; $and = 'and'; $parts = []; $where = ''; $values = [];
        $wildcard = false;
        $prefix = '';
        extract($options, EXTR_IF_EXISTS);

        // Normalize keys
        $params = array_change_key_case($params);

        $orsToJoin = [];
        if (isset($params[$or])) {
            $orsToJoin = array_merge($orsToJoin, $params[$or]);
        }

        $andsToJoin = [];
        if (isset($params[$and])) {
            $andsToJoin = array_merge($andsToJoin, $params[$and]);
        }

        // By default all params are joined with AND
        $opKeys = [$or => '', $and => ''];
        $withNoOperator = array_diff_key($params, $opKeys);
        $andsToJoin = array_merge($andsToJoin, $withNoOperator);

        // Build the where parts
        $operators = [$or => $orsToJoin, $and => $andsToJoin];
        foreach ($operators as $op => $items) {
            $toJoin = [];
            $defaultPrefix = $prefix;
            foreach ( $items as $key => $value ) {
                if ($prefixAndKey = static::extractPrefixAndKey($key)) {
                    list($prefix, $key) = $prefixAndKey;
                } else {
                    list($prefix, $key) = [$defaultPrefix, $key];
                }

                // When the value is an array join then all together withing an IN clause
                if (is_array($value)) {
                    $bindings = []; $inValues = [];
                    $valuesForInClause = array_filter(
                        $value,
                        function($v) {
                            return !preg_match('/^\!.*$/', $v);
                        }
                    );
                    array_walk(
                        $valuesForInClause,
                        function($v, $k) use($key, &$bindings, &$inValues) {
                            // Build the placeholders
                            $bindings[$k] = ":$key$k";
                            // Set the values
                            $inValues["$key$k"] = $v;
                        }
                    );
                    if ($inValues) {
                        $bindings = implode(', ', $bindings);
                        $toJoin[] = "$prefix`$key` IN ($bindings)";

                        $values = array_merge($values, $inValues);
                    }

                    $bindingOffset = count($valuesForInClause);
                    $bindings = []; $notinValues = [];
                    $valuesForNotInClause = array_filter(
                        $value,
                        function($v) {
                            return preg_match('/^\!.*$/', $v);
                        }
                    );
                    array_walk(
                        $valuesForNotInClause,
                        function($v, $k) use($key, &$bindings, &$notinValues, $bindingOffset) {
                            // $k += $bindingOffset;
                            // Build the placeholders
                            $bindings[$k] = ":$key$k";
                            // Set the values
                            $notinValues["$key$k"] = str_replace('!', '', $v);
                        }
                    );
                    if ($notinValues) {
                        $bindings = implode(', ', $bindings);
                        $toJoin[] = "$prefix`$key` NOT IN ($bindings)";

                        $values = array_merge($values, $notinValues);
                    }
                }
                elseif(preg_match('/^\!NULL$/', $value)) {
                    $toJoin[] = "$prefix`$key` IS NOT NULL";
                }
                elseif(preg_match('/^NULL$/', $value)) {
                    $toJoin[] = "$prefix`$key` IS NULL";
                }
                else {
                    if ($wildcard && preg_match('/^\*.*\*$/', $value)) {
                        // Matches *value*
                        $value = str_replace('*', '%', $value);
                        $toJoin[] = "$prefix`$key` LIKE :$key";
                    }
                    elseif (preg_match('/^\!.*$/', $value)) {
                        // Matches !value
                        $value = str_replace('!', '', $value);
                        $toJoin[] = "$prefix`$key` <> :$key";
                    }
                    elseif (preg_match('/^\>=.*$/', $value)) {
                        // Matches >=value
                        $value = str_replace('>=', '', $value);
                        $toJoin[] = "$prefix`$key` >= :$key";
                    }
                    elseif (preg_match('/^\>.*$/', $value)) {
                        // Matches >value
                        $value = str_replace('>', '', $value);
                        $toJoin[] = "$prefix`$key` > :$key";
                    }
                    elseif (preg_match('/^\<=.*$/', $value)) {
                        // Matches <=value
                        $value = str_replace('<=', '', $value);
                        $toJoin[] = "$prefix`$key` <= :$key";
                    }
                    elseif (preg_match('/^\<.*$/', $value)) {
                        // Matches <value
                        $value = str_replace('<', '', $value);
                        $toJoin[] = "$prefix`$key` < :$key";
                    }
                    elseif (preg_match('/^\#\{.*\}$/msU', $value)) {
                        // Matches #{value}
                        $literal = preg_replace('/^\#\{(.*)\}$/msU', '$1', $value);
                        $toJoin[] = "($prefix`$key` $literal)";
                        // Do no add a value since its a literal
                        unset($value);
                    }
                    else {
                        $toJoin[] = "$prefix`$key` = :$key";
                    }

                    if (isset($value)) {
                        $values[$key] = $value;
                    }
                }
            }
            $glue = strtoupper($op);
            $parts[$op] = implode(" $glue ", $toJoin);
        }

        if ($parts[$or] && $parts[$and]) {
            // When both operators are there wrap the OR statements with parenthesis
            $parts[$or] = "({$parts[$or]})";
            if (count($orsToJoin) == 1) {
                $parts[$and] = "({$parts[$and]})";
            }
        }
        $where = array_values($parts);
        $where = array_filter($where);
        if (count($orsToJoin) == 1) {
            // Support one single OR
            $where = implode(" OR ", $where);
        } else {
            $where = implode(" AND ", $where);
        }
        return [$where, $values];
    }

    private static function extractPrefixAndKey($key)
    {
        $prefixAndKey = explode('.', $key);
        if (count($prefixAndKey) > 1) {
            // Add the trailing dot to the prefix
            $prefixAndKey[0] .= '.';
            return $prefixAndKey;
        }
    }
}
