<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use DB;

class ApiModel extends Model
{
	/**
	 * Funcão...
	 *
	 **/
    public function get_data($dataset, $fields = null, $order = null, $limit = null, $format = null, $refine = null, $exclude = null){
    	$conn = DB::connection($dataset);
        $type = $conn->getConfig('driver');

        switch ($type) {
            case 'mysql':
                $result = $this->mysql($conn, $fields, $order, $limit, $refine, $exclude);
                break;
            case 'pgsql':
                $result = $this->postgres($conn, $fields, $order, $limit, $refine, $exclude);
                break;
            case 'mongodb':
                $result = $this->mongodb($conn, $fields, $order, $limit, $refine, $exclude);
                break;
        }

    	return $result;
    }

    private function mysql($conn, $fields = null, $order = null, $limit = null, $refine = null, $exclude = null) {
        // variaveis configuradas
        $c_fields = $conn->getConfig('fields');
        $c_from = $conn->getConfig('from');
        $c_join = $conn->getConfig('join');
        $c_order = $conn->getConfig('order');
        $c_where = $conn->getConfig('where');
        $c_limit = $conn->getConfig('limit');
        $c_group = $conn->getConfig('groupBy');

        // montagem da consulta ao banco
        $query = $conn->table($c_from);

        // organiza os joins (4 em 4 valores do array)
        if (!empty($c_join)) {
            for ($i = 0; $i < count($c_join); $i+=4) {
                $query = $query->join($c_join[$i], $c_join[$i+1], $c_join[$i+2], $c_join[$i+3]);
            }
        }

        // organiza os campos
        if (empty($fields)) {
            if (empty($c_fields)) {
                $fields = array('*');
            } else {
                $fields = $c_fields;
            }
        }
        $query = call_user_func_array(array($query, 'select'), $fields);

        // organiza as restrições
        if (!empty($c_where)) {
            for ($i = 0; $i < count($c_where); $i+=3) {
                $query = $query->where($c_where[$i], $c_where[$i+1], $c_where[$i+2]);
            }
            if (!empty($refine)) {
                foreach ($refine AS $key => $value) {
                    $query = $query->where($key, '=', $value);
                }
            }

            if (!empty($exclude)) {
                foreach ($exclude AS $key => $value) {
                    $query = $query->where($key, '<>', $value);
                }
            }
        } else {
            if (!empty($refine)) {
                foreach ($refine AS $key => $value) {
                    $query = $query->where($key, '=', $value);
                }
            }

            if (!empty($exclude)) {
                foreach ($exclude AS $key => $value) {
                    $query = $query->where($key, '<>', $value);
                }
            }
        }

        // organiza a ordenação
        if (empty($order) && !empty($c_order)) {
            $query = $query->orderBy($c_order[0], $c_order[1]);
        } else if (!empty($order)) {
            $order = explode(' ', $order);
            $query = $query->orderBy($order[0], $order[1]);
        }

        // organiza o limite
        if (empty($limit) && !empty($c_limit)) {
            $query = $query->limit($c_limit);
        } else if (!empty($limit)) {
            $query = $query->limit($limit);
        }

        // organiza o group by
        if (!empty($c_group)) {
            $query = $query->groupBy($c_group);
        }

        // retorna o resultado geral da consulta
        return $query->get();
    }

    private function postgres($conn, $fields = null, $sort = null, $limit = null, $refine = null, $exclude = null) {

        // variaveis configuradas
        $c_fields = $conn->getConfig('fields');
        $c_from = $conn->getConfig('from');
        $c_join = $conn->getConfig('join');
        $c_order = $conn->getConfig('order');
        $c_where = $conn->getConfig('where');
        $c_limit = $conn->getConfig('limit');
        $c_group = $conn->getConfig('groupBy');

        // montagem da consulta ao banco
        $query = $conn->table($c_from);

        // organiza os joins (4 em 4 valores do array)
        if (!empty($c_join)) {
            for ($i = 0; $i < count($c_join); $i+=4) {
                $query = $query->join($c_join[$i], $c_join[$i+1], $c_join[$i+2], $c_join[$i+3]);
            }
        }

        // organiza os campos
        if (empty($fields)) {
            if (empty($c_fields)) {
                $fields = array('*');
            } else {
                $fields = $c_fields;
            }
        } else {

        }
        $query = call_user_func_array(array($query, 'select'), $fields);

        // organiza as restrições
        if (!empty($c_where)) {
            for ($i = 0; $i < count($c_where); $i+=3) {
                $query = $query->where($c_where[$i], $c_where[$i+1], $c_where[$i+2]);
            }
            if (!empty($refine)) {
                foreach ($refine AS $key => $value) {
                    $query = $query->where($key, '=', $value);
                }
            }

            if (!empty($exclude)) {
                foreach ($exclude AS $key => $value) {
                    $query = $query->where($key, '<>', $value);
                }
            }
        } else {
            if (!empty($refine)) {
                foreach ($refine AS $key => $value) {
                    $query = $query->where($key, '=', $value);
                }
            }

            if (!empty($exclude)) {
                foreach ($exclude AS $key => $value) {
                    $query = $query->where($key, '<>', $value);
                }
            }
        }

        // organiza a ordenação
        if (empty($order) && !empty($c_order)) {
            $query = $query->orderBy($c_order[0], $c_order[1]);
        } else if (!empty($order)) {
            $order = explode(' ', $order);
            $query = $query->orderBy($order[0], $order[1]);
        }

        // organiza o limite
        if (empty($limit) && !empty($c_limit)) {
            $query = $query->limit($c_limit);
        } else if (!empty($limit)) {
            $query = $query->limit($limit);
        }

        // organiza o group by
        if (!empty($c_group)) {
            $query = $query->groupBy($c_group);
        }

        // retorna o resultado geral da consulta
        return $query->get();
    }

    private function mongodb($conn, $fields = null, $sort = null, $limit = null, $refine = null, $exclude = null) {
        // variaveis configuradas
        $c_fields = $conn->getConfig('fields');
        $c_collection = $conn->getConfig('collection');
        $c_join = $conn->getConfig('join');
        $c_order = $conn->getConfig('order');
        $c_where = $conn->getConfig('where');
        $c_limit = $conn->getConfig('limit');
        $c_group = $conn->getConfig('groupBy');

        // montagem da consulta ao banco
        $query = $conn->collection($c_collection);

        // organiza os joins (4 em 4 valores do array)
        if (!empty($c_join)) {
            for ($i = 0; $i < count($c_join); $i+=4) {
                $query = $query->join($c_join[$i], $c_join[$i+1], $c_join[$i+2], $c_join[$i+3]);
            }
        }

        // organiza os campos
        if (empty($fields)) {
            if (empty($c_fields)) {
                $fields = array('*');
            } else {
                $fields = $c_fields;
            }
        }
        $query = call_user_func_array(array($query, 'select'), $fields);

        // organiza as restrições
        if (!empty($c_where)) {
            for ($i = 0; $i < count($c_where); $i+=3) {
                $query = $query->where($c_where[$i], $c_where[$i+1], $c_where[$i+2]);
            }
            if (!empty($refine)) {
                foreach ($refine AS $key => $value) {
                    foreach ($value AS $v) {
                        if (is_numeric($v)) {
                            $v = (strpos($v, '.') !== false) ? floatval($v) : intval($v);
                        }

                        $query = $query->where($key, '=', $v);
                    }
                }
            }

            if (!empty($exclude)) {
                foreach ($exclude AS $key => $value) {
                    foreach ($value AS $v) {
                        if (is_numeric($v)) {
                            $v = (strpos($v, '.') !== false) ? floatval($v) : intval($v);
                        }

                        $query = $query->where($key, '<>', $v);
                    }
                }
            }
        } else {
            if (!empty($refine)) {
                foreach ($refine AS $key => $value) {
                    foreach ($value AS $v) {
                        if (is_numeric($v)) {
                            $v = (strpos($v, '.') !== false) ? floatval($v) : intval($v);
                        }

                        $query = $query->where($key, '=', $v);
                    }
                }
            }

            if (!empty($exclude)) {
                foreach ($exclude AS $key => $value) {
                    foreach ($value AS $v) {
                        if (is_numeric($v)) {
                            $v = (strpos($v, '.') !== false) ? floatval($v) : intval($v);
                        }

                        $query = $query->where($key, '<>', $v);
                    }
                }
            }
        }

        // organiza a ordenação
        if (empty($order) && !empty($c_order)) {
            $query = $query->orderBy($c_order[0], $c_order[1]);
        } else if (!empty($order)) {
            $order = explode(' ', $order);
            $query = $query->orderBy($order[0], $order[1]);
        }

        // organiza o limite
        if (empty($limit) && !empty($c_limit)) {
            $query = $query->limit($c_limit);
        } else if (!empty($limit)) {
            $query = $query->limit($limit);
        }

        // organiza o group by
        if (!empty($c_group)) {
            $query = $query->groupBy($c_group);
        }

        // retorna o resultado geral da consulta
        return $query->get();
    }

    private function redis() {
    	
    }

    private function voltdb() {
    	
    }

    private function memsql() {
    	
    }
}