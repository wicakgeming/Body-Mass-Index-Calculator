<?php
session_start();
if ($_SESSION['level'] == "") {
    header("location:index.php?pesan=gagal");
}

include_once "webapi/config/connection.php";

$database = new Database();
$conn = $database->getConnection();

$query = $conn->query("SELECT * FROM bmi");
if ($query) {
    $data = $query->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $tinggi = $data['tinggi'];
        $berat = $data['berat'];
        $hasil = $data['hasil'];
        $hasil_char = $data['hasil_char'];
    } else {
        echo "No data found.";
    }
} else {
    echo "Error executing the query.";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style/page-style.css">

    <title>BMI Calculator</title>
</head>

<body>
    <header>
        <div class="header-blue">
            <nav class="navbar navbar-dark navbar-expand-md navigation-clean-search">
                <div class="container"><a class="navbar-brand" href="https://instagram.com/retnm20">CV Reinmas Persada
                        Abadi</a><button class="navbar-toggler" data-toggle="collapse" data-target="#navcol-1"><span
                            class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
                    <div class="collapse navbar-collapse" id="navcol-1">
                        <ul class="nav navbar-nav">
                            <li class="nav-item" role="presentation"><a class="nav-link active" href="home.php">Home</a>
                            </li>
                            <li class="dropdown"><a class="dropdown-toggle nav-link dropdown-toggle"
                                    data-toggle="dropdown" aria-expanded="false" href="#">Lainnya </a>
                                <div class="dropdown-menu" role="menu"><a class="dropdown-item" role="presentation"
                                        href="about.php">About</a><a class="dropdown-item" role="presentation"
                                        href="how.php">Cara Kerja</a>
                            </li>
                        </ul>
                        <form class="form-inline mr-auto" target="_self">
                        </form><span class="navbar-text">
                            Hi!
                            <?php echo $_SESSION['username']; ?>
                        </span>
                        <a class="btn btn-light action-button" role="button" href="login/logout.php" style="margin-left: 10px;">Log Out</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>BMI Calculator</h1>
            <div class="card">
                <label for="bmi">Skor BMI Kamu</label>
                <?php
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("SELECT hasil FROM bmi ORDER BY id DESC LIMIT 1");
                $stmt->execute();
                $stmt->bindColumn('hasil', $hasil_bmi);
                $stmt->fetch();

                echo '<div class="card-main">';
                echo '<span class="ukuran-angka">' . $hasil_bmi . '</span>';
                echo '</div>';
                ?>
                <div class="hasil">
                    <?php
                    $stmt = $conn->prepare("SELECT hasil_char FROM bmi ORDER BY id DESC LIMIT 1");
                    $stmt->execute();
                    $stmt->bindColumn('hasil_char', $hasil_char);
                    $stmt->fetch();

                    echo '<div class="card-main">';
                    echo '<span class="ukuran-hasil">' . $hasil_char . '</span>';
                    echo '</div>';
                    ?>
                </div>

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                    Detail
                </button>

                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <?php
                                $query = $conn->query("SELECT * FROM bmi ORDER BY id DESC LIMIT 1");

                                if ($query) {
                                    $data = $query->fetch(PDO::FETCH_ASSOC);
                            
                                    if ($data) {
                                        $tinggi = $data['tinggi'];
                                        $berat = $data['berat'];
                                        $hasil = $data['hasil'];
                                        $hasilchar = $data['hasil_char'];
                            
                                        echo '<div class="modal-body">';
                                        echo '<table>';
                                        echo '<tr>';
                                        echo '<td>Tinggi Kamu</td>';
                                        echo '<td>: ' . $tinggi . '</td>';
                                        echo '</tr>';
                                        echo '<tr>';
                                        echo '<td>Berat Kamu</td>';
                                        echo '<td>: ' . $berat . '</td>';
                                        echo '</tr>';
                                        echo '<tr>';
                                        echo '<td>Catatan</td>';
                                        echo '<td>: ' . $hasilchar . '</td>';
                                        echo '</tr>';
                                        echo '</table>';
                                        echo '</div>';
                                    } else {
                                        echo "No data found.";
                                    }
                                } else {
                                    echo "Error executing the query.";
                                }
                            
                            ?>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy;2024 Reftito Dimas </p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>