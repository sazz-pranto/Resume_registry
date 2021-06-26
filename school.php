<?php 
    session_start();
    if ( !isset($_SESSION['name']) ) {
        die('Must be logged in');
    }
    if ( !isset($_REQUEST['term']) ) {
        die('Missing required parameter');
    }
    require_once "pdo.php";
    header('Content-Type: application/json; charset=utf-8');

    $stmt = $pdo->prepare('SELECT `name` FROM `institution` WHERE `name` LIKE :prefix');
    $stmt->execute(array( ':prefix' => "%".$_REQUEST['term']."%"));
    $retval = array();
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $retval[] = $row['name'];
    }

    echo(json_encode($retval, JSON_PRETTY_PRINT));
    // echo "<p>'.$names.'</p>";

?>