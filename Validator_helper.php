<?php 

class Validator_helper
{
    protected $fieldName;
    protected $fieldAlias;
    protected $fieldValue;
    protected $method;
    protected $errors = [];

    public function __construct() {
        $this->CI =& get_instance();
    }

    // set field yang akan di validasi
    public function setField($field, $rules = [], $method = "post")
    {
        $this->fieldValue = $this->CI->input->$method($field);
        $this->fieldAlias = str_replace("_", " ", ucfirst($field));
        $this->fieldName = $field;
        $this->method = $method;
        
        if ( is_array($this->fieldValue) ) {
            $field_alias =  str_replace("[]", "", ucfirst($this->fieldAlias));
            $field_index = str_replace("[]", "", $this->fieldName);
            foreach ($this->fieldValue as $key => $value) {
                $this->fieldValue = $value;
            
                $this->fieldAlias = $field_alias . " " . $key;
                $this->fieldName = $field_index . "_" . $key;

                $this->setValidator($rules);
            }
        } else {
            $this->setValidator($rules);
        }

        return $this;
    }

    // generate validator
    protected function setValidator($rules)
    {
        foreach ($rules as $rule) {
            $data   = explode(":", $rule);
            $func   = $data[0];
            $value  = $data[1] ?? "";

            $this->$func($value);
        }
    }

    // validasi required
    protected function required()
    {
        if ( strlen($this->fieldValue) == 0 ) {
            $this->setErrors("$this->fieldAlias wajib diisi");
        }
    }

    // minimal karakter
    protected function min($val)
    {
        if ( strlen($this->fieldValue) < $val ) {
            $this->setErrors("$this->fieldAlias  minimal $val karakter");
        }
    }
    
    // maksimal karakter
    protected function max($val)
    {
        if ( strlen($this->fieldValue) > $val ) {
            $this->setErrors("$this->fieldAlias  maksimal $val karakter");
        }
    }

    // hanya huruf dan spasi
    protected function alpha_space()
    {
        if ( !preg_match("/(^[a-zA-Z ]+$)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias hanya berupa huruf dan spasi");
        }
    }
    
    // hanya huruf dan angka
    protected function alpha_numeric()
    {
        if ( !preg_match("/(^[a-zA-Z0-9]+$)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias hanya berupa huruf dan angka");
        }
    }
    
    // hanya angka
    protected function numeric()
    {
        if ( !preg_match("/(^[0-9]+$)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias hanya berupa angka");
        }
    }
    
    // regex menggunakan aturan sendiri
    protected function regex($val)
    {
        if ( !preg_match("/$val/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias tidak valid");
        }
    }
    
    // exists pada database
    protected function db_exists($val)
    {
        $data = explode(",", $val);
        $table     = $data[0];
        $column     = $data[1];

        $query = "
        SELECT COUNT(*) AS count FROM $table WHERE $column = '$this->fieldValue'
        ";
        $result = $this->CI->db->query($query);
        $valid = $result->row();

        if ( $valid->count < 1 ) {
            $this->setErrors("$this->fieldAlias tidak ada pada database");
        }
    }
    
    // unique pada database
    protected function db_unique($val)
    {
        $data = explode(",", $val);
        $table      = $data[0];
        $column     = $data[1];
        $except     = $data[2] ?? "";
        $exceptVal  = $data[3] ?? "";

        $query = "
        SELECT * FROM $table WHERE $column = '$this->fieldValue'
        ";

        $result = $this->CI->db->query($query);
        $rows = $result->row();

        if ( $rows && $exceptVal != $rows->$except ) {
            $this->setErrors("$this->fieldAlias telah digunakan sebelumnya");
        }
    }

    // harus sama dengan data yang ada
    protected function in($val)
    {
        $data = explode(",", $val);
        if ( !in_array($this->fieldValue, $data) ) {
            $this->setErrors("$this->fieldAlias tidak valid");
        }
    }

    // validasi konfirmasi
    protected function confirm()
    {
        $method = $this->method;
        $confirm = $this->CI->input->$method($this->fieldName . "_confirm");

        if ( $this->fieldValue != $confirm ) {
            $this->setErrors("Konfirmasi ".strtolower($this->fieldAlias)." tidak sama");
        }
    }
    
    // validasi password
    protected function password()
    {
        if ( !preg_match("/([a-z]+)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias setidak nya memiliki 1 huruf kecil");
        } elseif ( !preg_match("/([A-Z]+)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias setidak nya memiliki 1 huruf kapital");
        } elseif ( !preg_match("/([0-9]+)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldAlias setidak nya memiliki 1 huruf angka");
        }
    }

    // validasi email
    protected function email()
    {
        if ( !filter_var($this->fieldValue, FILTER_VALIDATE_EMAIL) ) {
            $this->setErrors("$this->fieldAlias tidak valid");
        }
    }
    
    // validasi url
    protected function url()
    {
        if ( !filter_var($this->fieldValue, FILTER_VALIDATE_URL) ) {
            $this->setErrors("$this->fieldAlias harus alamat URL yang valid");
        }
    }

    // distinct
    protected function distinct()
    {
        $unique = array_unique($this->fieldValue);
        if ( count($this->fieldValue) != count($unique) ) {
            $this->setErrors("$this->fieldAlias memiliki duplikasi data");
        }
    }

    // validasi file extension
    protected function extension($val)
    {
        $validExtension = explode(",", $val);
        $file = $_FILES[$this->fieldName]['name'];

        if ( !in_array(pathinfo($file, PATHINFO_EXTENSION), $validExtension) ) {
            $this->setErrors("$this->fieldAlias harus merupakan tipe file: $val");
        }
    }

    // validasi file size upload
    protected function file_size($val)
    {
        $file = $_FILES[$this->fieldName]['size'];
        
        if ( $file > $val ) {
            $this->setErrors("$this->fieldAlias maksimal $val bytes");
        }
    }

    // file required
    protected function file_required()
    {
        $file = $_FILES[$this->fieldName];

        if ( is_array($file['error']) ) {
            $field_alias =  str_replace("[]", "", ucfirst($this->fieldAlias));
            $field_index = str_replace("[]", "", $this->fieldName);
            foreach ($file['error'] as $key => $value) {
                if ( $value != 0 ) {
                    $this->fieldAlias = $field_alias . " " . $key;
                    $this->fieldName = $field_index . "_" . $key;
                    $this->setErrors("$this->fieldAlias wajib diisi");
                }
            }
        } else {
            if ( $file['error'] != 0 ) {
                $this->setErrors("$this->fieldAlias wajib diisi");
            }
        }
    }

    // mengatur error
    protected function setErrors($message)
    {
        if ( !array_key_exists($this->fieldName, $this->errors) ) {
            $this->errors[$this->fieldName] = $message;
        }
    }

    // mengambil data error
    public function getErrors()
    {
        return $this->errors;
    }
}