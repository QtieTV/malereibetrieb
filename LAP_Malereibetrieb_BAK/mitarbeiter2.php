<?php
require("includes/common.inc.php");
require("includes/config.inc.php");
require("includes/conn.inc.php");

if(count($_POST)==0) {
    $_POST["NN_MA"] = "";
    $_POST["VN_MA"] = "";
    $_POST["NN_KD"] = "";
    $_POST["VN_KD"] = "";
}

// Get the current page number from the query string
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 2; // Number of members per page

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Mitarbeiterauswertung</title>
</head>

<body>
    <h1>Mitarbeiterauswertung</h1>
    <form method="post">
        <fieldset>
            <legend>Mitarbeiter</legend>
            <label>
                Nachname:
                <input type="text" name="NN_MA" value="<?php echo($_POST["NN_MA"]); ?>">
            </label>
            <label>
                Vorname:
                <input type="text" name="VN_MA" value="<?php echo($_POST["VN_MA"]); ?>">
            </label>
        </fieldset>
        <fieldset>
            <legend>Kunde</legend>
            <label>
                Nachname:
                <input type="text" name="NN_KD" value="<?php echo($_POST["NN_KD"]); ?>">
            </label>
            <label>
                Vorname:
                <input type="text" name="VN_KD" value="<?php echo($_POST["VN_KD"]); ?>">
            </label>
        </fieldset>
        <input type="submit" value="filtern">
    </form>
    <?php
    $where = "";
    $arr_W = [];
    if(count($_POST) > 0) {
        if(strlen($_POST["VN_MA"]) > 0) {
            $arr_W[] = "tbl_mitarbeiter.Vorname='" . $_POST["VN_MA"] . "'";
        }
        if(strlen($_POST["NN_MA"])> 0 ) {
            $arr_W[] = "tbl_mitarbeiter.Nachname='" . $_POST["NN_MA"] . "'";
        }
        
        if(count($arr_W) > 0) {
            $where = "
                WHERE(" . implode(" AND ",$arr_W) . ")
            ";
        }
    }
    $sql = "
        SELECT COUNT(*) as total FROM tbl_mitarbeiter
        " . $where . "
    ";
    $result = $GLOBALS["conn"]->query($sql) or die("Fehler in der Query: " . $GLOBALS["conn"]->error . "<br>" . $sql);
    $row = $result->fetch_assoc();
    $total_pages = ceil($row['total'] / $limit);

    $offset = ($page - 1) * $limit;
    $sql = "
        SELECT * FROM tbl_mitarbeiter
        " . $where . "
        ORDER BY Nachname ASC, Vorname ASC
        LIMIT $offset, $limit
    ";
    $ma_liste = $GLOBALS["conn"]->query($sql) or die("Fehler in der Query: " . $GLOBALS["conn"]->error . "<br>" . $sql);
    if($ma_liste->num_rows > 0) {
        echo('<ul>');
        while($ma = $ma_liste->fetch_object()) {
            echo('
                <li>
                    ' . $ma->Nachname . ' ' . $ma->Vorname . ':
                    <ul>
            ');
            
            $where = "";
            $arr_W = ["tbl_einsatz.FIDMitarbeiter=" . $ma->IDMitarbeiter];
            if(count($_POST) > 0) {
                if(strlen($_POST["VN_KD"]) > 0) {
                    $arr_W[] = "tbl_kunden.Vorname='" . $_POST["VN_KD"] . "'";
                }
                if(strlen($_POST["NN_KD"]) > 0) {
                    $arr_W[] = "tbl_kunden.Nachname='" . $_POST["NN_KD"] . "'";
                }
            }
            
            $sql = "
                SELECT
                    tbl_kunden.Nachname,
                    tbl_kunden.Vorname,
                    tbl_kunden.Adresse,
                    tbl_kunden.PLZ,
                    tbl_kunden.Ort,
                    tbl_einsatz.Startzeitpunkt,
                    tbl_einsatz.Endzeitpunkt
                FROM tbl_einsatz
                LEFT JOIN tbl_kunden ON tbl_kunden.IDKunde=tbl_einsatz.FIDKunde
                WHERE(
                    " . implode(" AND ",$arr_W) . "
                )
                ORDER BY tbl_einsatz.Startzeitpunkt ASC
            ";
            $einsaetze = $GLOBALS["conn"]->query($sql) or die("Fehler in der Query: " . $GLOBALS["conn"]->error . "<br>" . $sql);
            while($einsatz = $einsaetze->fetch_object()) {
                echo('
                    <li>
                        ' . date("j.n.Y, H:i",strtotime($einsatz->Startzeitpunkt)) . ' bis ' . date("H:i",strtotime($einsatz->Endzeitpunkt)) . ': ' . $einsatz->Nachname. ' ' . $einsatz->Vorname . ' (' . $einsatz->Adresse . ', ' . $einsatz->PLZ . ' ' . $einsatz->Ort . ')
                    </li>
                ');
            }
            
            echo('        
                    </ul>
                </li>
            ');
        }
        echo('</ul>');

        // Display the paging menu
        for ($i = 1; $i <= $total_pages; $i++) {
            echo('<a href="?page=' . $i . '">' . $i . '</a> ');
        }
    }
    ?>
</body>
</html>
