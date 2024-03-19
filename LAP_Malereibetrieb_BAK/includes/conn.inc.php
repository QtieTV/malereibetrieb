<?php
// conn.inc.php
function conn() {
    $conn = new MySQLi(DB["host"],DB["user"],DB["pwd"],DB["name"]);
    if($conn->connect_errno>0) {
        if(TESTMODUS>0) {
            die("Fehler im Verbindungsaufbau: " . $conn->connect_error);
        }
        else {
            header("Location: errors/db_connect.html");
        }
    }
    
    $conn->set_charset("UTF8");
    
    return $conn;
}

$GLOBALS["conn"] = conn();
?>