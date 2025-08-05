<?php

require_once __DIR__ . '/Env.php';

class RawSQL
{
    public $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
}


class Database
{
    public $db;

    // Constructor: Initializes the db
    public function __construct()
    {
        // $env = new Env();
        // Create a new MySQLi db
        $this->db = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME
        );

        // Check for db errors
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    private function get_stmt_var_type($val)
    {
        if (is_int($val)) {
            return 'i';
        } elseif (is_float($val)) {
            return 'd';
        } elseif (is_string($val)) {
            return 's';
        } else {
            return 'b'; // blob and other types
        }
    }

    public function select($tableName, $columns = [], $extra_sql = "", $array_val_extra = [])
    {
        $values = [];
        if (count($columns) > 0) {
            $columnsStr = implode(', ', $columns);
        } else {
            $columnsStr = "*";
        }
        $sql = "SELECT $columnsStr FROM $tableName $extra_sql";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $types = '';
        foreach ($array_val_extra as $k => $extra_v) {
            $types .= $this->get_stmt_var_type($extra_v);
            $values[] = $extra_v;
        }
        if (count($array_val_extra) > 0) {
            $stmt->bind_param($types, ...$values);
        }
        $success = $stmt->execute();
        if (!$success) {
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        if ($result->num_rows <= 0) {
            $stmt->close();
            return [];
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    public function insert($tableName, $data = [], $extra_sql = "", $array_val_extra = [])
    {
        $columns = [];
        $placeholders = [];
        $values = [];
        $types = '';

        foreach ($data as $column => $value) {
            $columns[] = $column;

            if ($value instanceof RawSQL) {
                // Use the raw SQL directly
                $placeholders[] = $value->value;
            } else {
                // Use a bind parameter
                $placeholders[] = '?';
                $values[] = $value;
                $types .= $this->get_stmt_var_type($value);
            }
        }

        // Add extra_sql values (for WHERE, RETURNING, etc.)
        foreach ($array_val_extra as $extra_v) {
            $values[] = $extra_v;
            $types .= $this->get_stmt_var_type($extra_v);
        }

        $columnsStr = implode(', ', $columns);
        $placeholdersStr = implode(', ', $placeholders);
        $sql = "INSERT INTO $tableName ($columnsStr) VALUES ($placeholdersStr) $extra_sql";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if ($types) {
            $stmt->bind_param($types, ...$values);
        }

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }


    public function get_last_id()
    {
        return $this->db->insert_id;
    }

    public function update($tableName, $data, $extra_sql, $array_val_extra)
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $columnsStr = implode('=?, ', $columns) . "=?";
        $sql = "UPDATE $tableName SET $columnsStr $extra_sql";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        // Determine the types for bind_param
        $types = '';
        foreach ($values as $value) {
            $types .= $this->get_stmt_var_type($value);
        }
        foreach ($array_val_extra as $k => $extra_v) {
            $types .= $this->get_stmt_var_type($extra_v);
            $values[] = $extra_v;
        }
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function delete($tableName, $extra_sql = "", $array_val_extra = [])
    {
        $sql = "DELETE FROM $tableName $extra_sql";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        // Determine the types for bind_param
        $types = '';
        $values = [];
        foreach ($array_val_extra as $k => $extra_v) {
            $types .= $this->get_stmt_var_type($extra_v);
            $values[] = $extra_v;
        }
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function raw_query_prepared($sql, $array_val_extra = [])
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if (!empty($array_val_extra)) {
            $types = '';
            $values = [];

            foreach ($array_val_extra as $extra_v) {
                $types .= $this->get_stmt_var_type($extra_v);
                $values[] = $extra_v;
            }

            // Only bind if $types is not empty
            if ($types !== '') {
                $stmt->bind_param($types, ...$values);
            }
        }

        $stmt->execute();

        // You may want to return result instead of just success flag
        $result = $stmt->get_result();
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : true;

        $stmt->close();
        return $data;
    }


    public function get_column_table($tableName)
    {
        $sql = "DESC $tableName";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $success = $stmt->execute();
        if (!$success) {
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        if ($result->num_rows <= 0) {
            $stmt->close();
            return [];
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    public function get_column_list($table, $ignore = [])
    {
        $ret = [];
        $columns = $this->get_column_table($table);
        foreach ($columns as $v) {
            if (
                count($ignore) > 0 &&
                in_array($v['Field'], $ignore)
            ) {
                continue;
            }
            $ret[] = $v['Field'];
        }
        return $ret;
    }

    public function duplicate_key_builder($columns)
    {
        $columnsStr = implode('=?, ', $columns) . "=?";
        return 'ON DUPLICATE KEY UPDATE ' . $columnsStr;
    }

    public function escape($str)
    {
        return $this->db->real_escape_string($str);
    }

    // Destructor: Closes the database db
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
