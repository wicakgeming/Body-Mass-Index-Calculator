<!DOCTYPE html>
<html>

<head>
    <title>Login BMI Calculator</title>
    <link rel="stylesheet" type="text/css" href="assets/style/style.css">
</head>

<body>
    <?php
    if (isset($_GET['pesan'])) {
        if ($_GET['pesan'] == "gagal") {
            echo "<div class='alert'>Username dan Password tidak sesuai !</div>";
        }
    }
    ?>p

    <div class="kotak_login">
        <p class="tulisan_login">Login untuk masuk</p>
        <form action="login/cek-login.php" method="post">
            <label>Username</label>
            <input type="text" name="username" class="form_login" required="required">

            <label>Password</label>
            <input type="password" name="password" class="form_login" required="required">

            <input type="submit" class="tombol_login" value="LOGIN">

            <br />
            <br />
        </form>

    </div>


</body>

</html>