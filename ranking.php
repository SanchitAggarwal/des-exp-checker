<?php

include "common.php.inc";
$dbh = getDBHandle();

if (isset($_GET)) {
    if (isset($_GET['myrank'])) {
        $uid = getUIDFromSID($dbh);
        if ($uid !== FALSE) {
            echo $uid;
        }
    }
    
    else {
        $query = 'SELECT username, total_files, flagged_files FROM users WHERE total_files > 0 ORDER BY total_files DESC';
        if (isset($_GET['limit'])) {
            if (is_numeric($_GET['limit'])) {
                $query .= ' LIMIT ?';
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(1, $_GET['limit'], PDO::PARAM_INT);
            }
        } else {
            $stmt = $dbh->prepare($query);
        }
        $stmt->execute();
        check_or_abort($stmt);
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (isset($_GET['output'])) {
            if ($_GET['output'] == "html") {
                if ($_GET['short'] == "true")
                    echo "<table class='table table-condensed table-striped'>";
                else
                    echo "<table class='table table-condensed table-striped'><thead><tr><th>Rank</th><th>Username</th><th>Problematic/Total</th><th># Files</th></tr></thead><tbody>\n";
                $total = null;
                $counter = 1;
                foreach ($response as $row) {
                    if ($total === null)
                        $total = $row['total_files'];
                    $width_flagged = (100*$row['flagged_files']/$total) . "%";
                    $width_total = (100*($row['total_files']-$row['flagged_files'])/$total) . "%";
                    echo "<tr><td># ". $counter ."</td><td><span class='namecol'>" . $row['username'] . "</span></td>";
                    echo "<td><div class='ratingcol'><span class='ratingbar bad' style='width:" . $width_flagged ."'></span>";
                    echo "<span class='ratingbar good' style='left:" . $width_flagged ."; width:" . $width_total ."'></span></div></td>";
                    echo "<td>" . $row['total_files'] . "</td></tr>\n";
                    $counter += 1;
                }
                echo "</tbody></table>";
            }
        }
        else 
            echo json_encode($response);
    }
}
?>