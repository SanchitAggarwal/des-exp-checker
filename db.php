<?php

include "common.php.inc";
$dbh = getDBHandle();

// check if POST data is present
if ($_POST) {
    $uid = getUIDFromSID($dbh);
    if ($uid) {
        // parse POST data and store each element in table qa
        $sth = $dbh->prepare('INSERT INTO qa (fileid, userid, problem, comment) VALUES (?, ?, ?, ?)');
        if (isset($_POST['problems'])) {
            $codes = getProblemCodes();
            foreach ($_POST['problems'] as $problem) {
                $code = $codes[$problem['problem']];
                $problem['x'] = (int) $problem['x'];
                $problem['y'] = (int) $problem['y'];
                unset($problem['problem']);
                if ($problem['detail'] == '')
                    unset($problem['detail']);
                // stores x,y, and (occasionally a free-form comment)
                $sth->execute(array($_POST['fileid'], $uid, $code, json_encode($problem)));
            }
            // update attached user database to reflect user action
            $sth2 = $dbh->prepare('UPDATE users SET total_files = total_files + 1, flagged_files = flagged_files + 1 WHERE rowid = ?');
            $sth2->execute(array($uid));
        } else {
            $sth->execute(array($_POST['fileid'], $uid, 0, null));
            $sth2 = $dbh->prepare('UPDATE users SET total_files = total_files + 1 WHERE rowid = ?');
            $sth2->execute(array($uid));
        }
    }
}

// return the next image
$res = getNextImage($dbh);
if ($res === False) {
    header('HTTP/1.0 500 Internal Server Error');
    exit(0);
}
$row = $res->fetch(PDO::FETCH_ASSOC);
echo json_encode($row);

?>