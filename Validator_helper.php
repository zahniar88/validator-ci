<?php 

class Validator_helper
{
    protected $fieldDefault;
    protected $fieldName;
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
        $this->fieldName = str_replace("_", " ", ucfirst($field));
        $this->fieldDefault = $field;
        $this->method = $method;

        foreach ($rules as $rule) {
            $data   = explode(":", $rule);
            $func   = $data[0];
            $value  = $data[1] ?? "";

            $this->$func($value);
        }

        return $this;
    }

    // validasi required
    protected function required()
    {
        if ( strlen($this->fieldValue) == 0 ) {
            $this->setErrors("$this->fieldName wajib diisi");
        }
    }

    // minimal karakter
    protected function min($val)
    {
        if ( strlen($this->fieldValue) < $val ) {
            $this->setErrors("$this->fieldName  minimal $val karakter");
        }
    }
    
    // maksimal karakter
    protected function max($val)
    {
        if ( strlen($this->fieldValue) > $val ) {
            $this->setErrors("$this->fieldName  maksimal $val karakter");
        }
    }

    // hanya huruf dan spasi
    protected function alpha_space()
    {
        if ( !preg_match("/(^[a-zA-Z ]+$)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName hanya berupa huruf dan spasi");
        }
    }
    
    // hanya huruf dan angka
    protected function alpha_numeric()
    {
        if ( !preg_match("/(^[a-zA-Z0-9]+$)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName hanya berupa huruf dan angka");
        }
    }
    
    // hanya angka
    protected function numeric()
    {
        if ( !preg_match("/(^[0-9]+$)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName hanya berupa angka");
        }
    }
    
    // regex menggunakan aturan sendiri
    protected function regex($val)
    {
        if ( !preg_match("/$val/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName tidak valid");
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
            $this->setErrors("$this->fieldName tidak ada pada database");
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
            $this->setErrors("$this->fieldName telah digunakan sebelumnya");
        }
    }

    // harus sama dengan data yang ada
    protected function in($val)
    {
        $data = explode(",", $val);
        if ( !in_array($this->fieldValue, $data) ) {
            $this->setErrors("$this->fieldName tidak valid");
        }
    }

    // validasi konfirmasi
    protected function confirm()
    {
        $method = $this->method;
        $confirm = $this->CI->input->$method($this->fieldDefault . "_confirm");

        if ( $this->fieldValue != $confirm ) {
            $this->setErrors("Konfirmasi ".strtolower($this->fieldName)." tidak sama");
        }
    }
    
    // validasi password
    protected function password()
    {
        if ( !preg_match("/([a-z]+)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName setidak nya memiliki 1 huruf kecil");
        } elseif ( !preg_match("/([A-Z]+)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName setidak nya memiliki 1 huruf kapital");
        } elseif ( !preg_match("/([0-9]+)/", $this->fieldValue) ) {
            $this->setErrors("$this->fieldName setidak nya memiliki 1 huruf angka");
        }
    }

    // validasi email
    protected function email()
    {
        if ( !filter_var($this->fieldValue, FILTER_VALIDATE_EMAIL) ) {
            $this->setErrors("$this->fieldName tidak valid");
        }
    }
    
    // validasi url
    protected function url()
    {
        if ( !filter_var($this->fieldValue, FILTER_VALIDATE_URL) ) {
            $this->setErrors("$this->fieldName harus alamat URL yang valid");
        }
    }

    // distinct
    protected function distinct()
    {
        $unique = array_unique($this->fieldValue);
        if ( count($this->fieldValue) != count($unique) ) {
            $this->setErrors("$this->fieldName memiliki duplikasi data");
        }
    }

    // validasi file extension
    protected function extension($val)
    {
        $validExtension = explode(",", $val);
        $file = $_FILES[$this->fieldDefault]['name'];

        if ( !in_array(pathinfo($file, PATHINFO_EXTENSION), $validExtension) ) {
            $this->setErrors("$this->fieldName harus merupakan tipe file: $val");
        }
    }

    // validasi file size upload
    protected function file_size($val)
    {
        $file = $_FILES[$this->fieldDefault]['size'];
        
        if ( $file > $val ) {
            $this->setErrors("$this->fieldName maksimal $val bytes");
        }
    }

    // mengatur error
    protected function setErrors($message)
    {
        if ( !array_key_exists($this->fieldDefault, $this->errors) ) {
            $this->errors[$this->fieldDefault] = $message;
        }
    }

    // mengambil data error
    public function getErrors()
    {
        return $this->errors;
    }
}