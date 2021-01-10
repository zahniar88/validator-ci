# Helper Validator
Helper validator dibuat untuk membantu proses validasi form pada codeigniter. Memiliki beberapa fitur validasi yang dapat digunakan. Terinspirasi dari validator laravel.

## Cara install
* Download file Validator_helper
* letakan pada folder helper codeigniter
* load file validator dengan cara
```php
$this->load->helper("validator_helper");
```

## Cara menggunakan
```php
$this->load->helper("validator_helper");
$validator = new Validator_helper;

$errors = $validator->setField("username", ["required", "max:30", "alpha_numeric"])
                    ->setField("password", ["required", "min:8", "confirm", "password"])
                    ->getErrors();
```

Jika terjadi error makan ```$errors``` akan menghasilkan output array seperti berikut:
```php
array(
    "username" => "Pesan kesalahan sesuai validasi",
    "password" => "Pesan kesalahan sesuai validasi"
);
```

peraturan penulisan pada ```setField``` seperti berikut:
```php
setField("nama_field", ["aturan"], "method");
// method: post || get -> default: post
```

## Fungsi yang tersedia
* Required
```php
setField("nama_field", ["aturan"], "method");
```

* Min panjang karakter
```php
setField("nama_field", ["min:angka"], "method");
```

* Max panjang karakter
```php
setField("nama_field", ["max:angka"], "method");
```

* Alpha Space hanya membolehkan huruf dan spasi
```php
setField("nama_field", ["alpha_space"], "method");
```

* Alpha numeric hanya untuk huruf dan angka
```php
setField("nama_field", ["alpha_space"], "method");
```

* Numeric hanya untuk angka
```php
setField("nama_field", ["alpha_space"], "method");
```

* Regex membuat aturan regex sendiri menggunakan preg_match
```php
setField("nama_field", ["regex:([a-z]+)"], "method");
```

* Db exists, data harus tersedia pada database
```php
setField("nama_field", ["db_exists:table,column"], "method");
```

* Db unique, data harus belum tersedia pada database
```php
setField("nama_field", ["db_unique:table,column,except,except_value"], "method");
```

* In, data harus sama dengan yang disediakan
```php
setField("nama_field", ["in:opsi1,opsi2"], "method");
```

* Confirm, menyamakan field dengan field_confirm
```php
setField("nama_field", ["confirm"], "method");
```

* Password, validasi password harus memiliki huruf besar, kecil dan angka minimal 1
```php
setField("nama_field", ["password"], "method");
```

* Email, field input harus merupakan alamat email yang valid
```php
setField("nama_field", ["email"], "method");
```

* Url harus merupakan alamat yang valid
```php
setField("nama_field", ["url"], "method");
```

* Distinct, input field array harus unique
```php
setField("nama_field", ["distinct"], "method");
```

* Extension, validasi extensi uploadfile
```php
setField("nama_field", ["extension:jpeg,png"], "method");
```

* File size, validasi ukuran upload file
```php
setField("nama_field", ["file_size:4000"], "method");
// size dalam bytes
```

Untuk sementera baru itu yang dapat saya buat. belum sesempuran milik laravel :)