<?php
$koneksi = mysqli_connect("localhost", "id*******_root", "******", "id********_bmical");

// Check connection
if (mysqli_connect_errno()) {
	echo "Koneksi database gagal : " . mysqli_connect_error();
}

?>