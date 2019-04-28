<?php
/**
 * mysql数据库驱动,完成对mysql数据库的操作
 * 修改为使用mysqli方式访问数据库
 * DexterLien 2019-4-28
 */

if (!defined('IN_XIAOCMS')) exit();

class mysql {

    public static $instance;
    public $db_link;

    /**
     * 构造函数
     */
    public function __construct($params = array()) {
        //检测参数信息是否完整
        if (!$params['host'] || !$params['username'] || !$params['dbname']) exit('mysql数据库配置文件不完整');
        //处理数据库端口
        if ($params['port'] && $params['port'] != 3306) $params['host'] .= ':' . $params['port'];
        //实例化mysql连接ID
        $this->db_link = @($GLOBALS["___mysqli_ston"] = mysqli_connect($params['host'],  $params['username'],  $params['password']));
        if (!$this->db_link) {
            exit('mysql服务器连接失败 ');
        } else {
            if (mysqli_select_db( $this->db_link, $params['dbname'])) {
                //设置数据库编码
                mysqli_query( $this->db_link, "SET NAMES {$params['charset']}");
                if (version_compare($this->get_server_info(), '5.0.2', '>=')) mysqli_query( $this->db_link, "SET SESSION SQL_MODE=''");
            } else {
                exit('mysql服务器无法连接数据库表' );
            }
        }
        return true;
    }

    /**
     * 执行SQL语句
     */
    public function query($sql) {
        $result = mysqli_query( $this->db_link, $sql);
        //获取当前运行的namespace、controller及action名称
        $namespace_id    = xiaocms::get_namespace_id();
        $controller_id    = xiaocms::get_controller_id();
        $action_id        = xiaocms::get_action_id();
        $namespace_code = $namespace_id ? '[' . $namespace_id . ']' : '';

        return $result;
    }

    /**
     * 获取mysql数据库服务器信息
     */
    public function get_server_info() {
        return ((is_null($___mysqli_res = mysqli_get_server_info($this->db_link))) ? false : $___mysqli_res);
    }

    /**
     * 获取mysql错误描述信息
     */
    public function error() {
        $error = ($this->db_link) ? mysqli_error($this->db_link) : mysqli_error($GLOBALS["___mysqli_ston"]);
        return function_exists('iconv') ? iconv('GBK', 'UTF-8', $error) : $error;
    }

    /**
     * 获取mysql错误信息代码
     */
    public function errno() {
        return ($this->db_link) ? mysqli_errno($this->db_link) : mysqli_errno($GLOBALS["___mysqli_ston"]);
    }

    /**
     * 通过一个SQL语句获取一行信息(字段型)
     */
    public function fetch_row($sql) {
        if (strtolower(substr($sql, 0, 6)) == 'select' && !stripos($sql, 'limit') !== false) $sql .= ' LIMIT 1';
        $result = $this->query($sql);
        if (!$result) return false;
        $rows   = mysqli_fetch_assoc($result);
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        return $rows;
    }

    /**
     * 通过一个SQL语句获取全部信息(字段型)
     */
    public function get_array($sql) {
        $result = $this->query($sql);
        if (!$result)return false;
        $myrow  = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $myrow[] = $row;
        }
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        return $myrow;
    }

    /**
     * 获取insert_id
     */
    public function insert_id() {
        return ($id = ((is_null($___mysqli_res = mysqli_insert_id($this->db_link))) ? false : $___mysqli_res)) >= 0 ? $id : mysql_result($this->query("SELECT last_insert_id()"));
    }

    /**
     * 字段的数量
     */
    public function num_fields($sql) {
        $result = $this->query($sql);
        return (($___mysqli_tmp = mysqli_num_fields($result)) ? $___mysqli_tmp : false);
    }

    /**
     * 结果集中的数量
     */
    public function num_rows($sql) {
        $result = $this->query($sql);
        return mysqli_num_rows($result);
    }

    /**
     * 获取字段类型
     */
    public function get_fields_type($table_name) {
        if (!$table_name) return false;
        $res   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM {$table_name}");
        $types = array();
        while ($row = (((($___mysqli_tmp = mysqli_fetch_field_direct($res, mysqli_field_tell($res))) && is_object($___mysqli_tmp)) ? ( (!is_null($___mysqli_tmp->primary_key = ($___mysqli_tmp->flags & MYSQLI_PRI_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->multiple_key = ($___mysqli_tmp->flags & MYSQLI_MULTIPLE_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->unique_key = ($___mysqli_tmp->flags & MYSQLI_UNIQUE_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->numeric = (int)(($___mysqli_tmp->type <= MYSQLI_TYPE_INT24) || ($___mysqli_tmp->type == MYSQLI_TYPE_YEAR) || ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? ($___mysqli_tmp->type == MYSQLI_TYPE_NEWDECIMAL) : 0)))) && (!is_null($___mysqli_tmp->blob = (int)in_array($___mysqli_tmp->type, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB)))) && (!is_null($___mysqli_tmp->unsigned = ($___mysqli_tmp->flags & MYSQLI_UNSIGNED_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->zerofill = ($___mysqli_tmp->flags & MYSQLI_ZEROFILL_FLAG) ? 1 : 0)) && (!is_null($___mysqli_type = $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = (($___mysqli_type == MYSQLI_TYPE_STRING) || ($___mysqli_type == MYSQLI_TYPE_VAR_STRING)) ? "type" : "")) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && in_array($___mysqli_type, array(MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT, MYSQLI_TYPE_LONG, MYSQLI_TYPE_LONGLONG, MYSQLI_TYPE_INT24))) ? "int" : $___mysqli_tmp->type)) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && in_array($___mysqli_type, array(MYSQLI_TYPE_FLOAT, MYSQLI_TYPE_DOUBLE, MYSQLI_TYPE_DECIMAL, ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? constant("MYSQLI_TYPE_NEWDECIMAL") : -1)))) ? "real" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_TIMESTAMP) ? "timestamp" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_YEAR) ? "year" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && (($___mysqli_type == MYSQLI_TYPE_DATE) || ($___mysqli_type == MYSQLI_TYPE_NEWDATE))) ? "date " : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_TIME) ? "time" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_SET) ? "set" : $___mysqli_tmp->type)) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_ENUM) ? "enum" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_GEOMETRY) ? "geometry" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_DATETIME) ? "datetime" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && (in_array($___mysqli_type, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB)))) ? "blob" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_NULL) ? "null" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type) ? "unknown" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->not_null = ($___mysqli_tmp->flags & MYSQLI_NOT_NULL_FLAG) ? 1 : 0)) ) : false ) ? $___mysqli_tmp : false)) {
            $types[$row->name] = $row->type;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        return $types;
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        if ($this->db_link) @((is_null($___mysqli_res = mysqli_close($this->db_link))) ? false : $___mysqli_res);
    }

    /**
     * 单例模式
     */
    public static function getInstance($params) {
        if (!self::$instance) {
            self::$instance = new self($params);
        }
        return self::$instance;
    }
}
