<?php
    class id21764929_bmical{

        // Connection
        private $conn;

        // Table
        private $db_table = "bmi";

        // Columns
        public $id;
        public $tinggi;
        public $berat;
        public $hasil;
        public $hasil_char;
        public $created_at;

        // Db connection
        public function __construct($db){
            $this->conn = $db;
        }

        // CREATE
        public function createLogData(){
            $sqlQuery = "INSERT INTO
                        ". $this->db_table ."
                    SET
                        tinggi = :tinggi, 
                        berat = :berat,
                        hasil = :hasil,
                        hasil_char = :hasil_char";
            $stmt = $this->conn->prepare($sqlQuery);
        
            // sanitize
            $this->tinggi=htmlspecialchars(strip_tags($this->tinggi));
            $this->berat=htmlspecialchars(strip_tags($this->berat));
            $this->hasil=htmlspecialchars(strip_tags($this->hasil));
            $this->hasil_char=htmlspecialchars(strip_tags($this->hasil_char));
        
            // bind data
            $stmt->bindParam(":tinggi", $this->tinggi);
            $stmt->bindParam(":berat", $this->berat);
            $stmt->bindParam(":hasil", $this->hasil);
            $stmt->bindParam(":hasil_char", $this->hasil_char);
            if($stmt->execute()){
               return true;
            }
            return false;
        }
    }
?>