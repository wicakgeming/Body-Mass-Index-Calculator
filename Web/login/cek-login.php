<?php
// mengaktifkan session pada php
session_start();

// menghubungkan php dengan koneksi database
include '../webapi/config/koneksi-login.php';

// menangkap data yang dikirim dari form login
$username = $_POST['username'];
$password = $_POST['password'];


// menyeleksi data user dengan username dan password yang sesuai
$login = mysqli_query($koneksi, "select * from user where username='$username' and password='$password'");
// menghitung jumlah data yang ditemukan
$cek = mysqli_num_rows($login);

if ($cek > 0) {

    $data = mysqli_fetch_assoc($login);

    if ($data['level'] == "admin") {
        $_SESSION['username'] = $username;
        $_SESSION['level'] = "admin";
        header("location:../home.php");

    } else if ($data['level'] == "user") {
        $_SESSION['username'] = $username;
        $_SESSION['level'] = "user";
        header("location:../home.php");
        
    } else {
        header("location:../index.php?pesan=gagal");
    }
} else {
    header("location:../index.php?pesan=gagal");
}

?>