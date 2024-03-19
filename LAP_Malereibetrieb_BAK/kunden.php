<?php
// kunden.php
require("includes/common.inc.php");
require("includes/config.inc.php");
require("includes/conn.inc.php");
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Kundenauswertung</title>
</head>

<body>
    <h1>Kundenauswertung</h1>
    <ul>
        <?php
        $sql = "
            SELECT * FROM tbl_kunden
            ORDER BY Nachname ASC, Vorname ASC
        ";
        $kunden = $GLOBALS["conn"]->query($sql) or die("Fehler in der Query: " . $GLOBALS["conn"]->error . "<br>" . $sql);
        while($kunde = $kunden->fetch_object()) {
            $sql = "
                SELECT
                    SUM(TIMESTAMPDIFF(MINUTE,Startzeitpunkt,Endzeitpunkt)) AS sumStunden,
                    MIN(Startzeitpunkt) AS von,
                    MAX(Endzeitpunkt) AS bis
                FROM tbl_einsatz
                WHERE(
                    FIDKunde=" . $kunde->IDKunde . "
                )
            ";
            $einsaetze = $GLOBALS["conn"]->query($sql) or die("Fehler in der Query: " . $GLOBALS["conn"]->error . "<br>" . $sql);
            $einsatz = $einsaetze->fetch_object();
            
            echo('
                <li>
                    ' . $kunde->Nachname . ' ' . $kunde->Vorname . ' (' . $kunde->Adresse . ', ' . $kunde->PLZ . ' ' . $kunde->Ort . ' | Tel: ' . $kunde->Telno . ' &bull; ' . $kunde->Email . '):
                    <ul>
                        <li>geleistete Stunden: ' . $einsatz->sumStunden/60 . ' h</li>
                        <li>abzurechnende Kosten: ' . $einsatz->sumStunden . ' EUR</li>
                        <li>von ' . $einsatz->von . ' bis ' . $einsatz->bis . '
                    </ul>
                </li>
            ');
        }
        ?>
    </ul>
</body>
</html>