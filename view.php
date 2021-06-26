<?php 
    require_once "pdo.php";
    require_once "utilities.php";
    session_start();
    if ( !isset($_SESSION['name']) ) {
        die('ACCESS DENIED');
    }
    if ( ! isset($_GET['profile_id']) ) {
        $_SESSION['error'] = "Missing profile_id";
        header('Location: index.php');
        return;
    }
    $stmt = $pdo->prepare("SELECT * FROM `profile` WHERE `profile_id`=:pid");
    $stmt->execute( array(':pid' => $_GET['profile_id'] ) );
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "NO DATA TO SHOW!";
    }
?>
<!DOCTYPE html>
<html>
<head>
<title>Sazzad Hossain's Profile View</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css"> 

<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

</head>
<body>
<?php 
    //load up the education and position rows
    $schools = loadEdu($pdo, $_GET['profile_id']);
    $positions = loadPos($pdo, $_GET['profile_id']);
?>
<div class="container">
    <h1>Profile information</h1>
    <p>First Name:
    <?php echo htmlentities($row['first_name']) ?></p>
    <p>Last Name:
    <?php echo htmlentities($row['last_name']) ?></p>
    <p>Email:
    <?php echo htmlentities($row['email']) ?></p>
    <p>Headline:<br/>
    <?php echo htmlentities($row['headline']) ?></p>
    <p>Summary:<br/>
    <?php echo htmlentities($row['summary']) ?><p>
    </p>
    <p>Education:<br/>
    <?php
        if(count($schools) > 0) {
            foreach( $schools as $school ) {
                echo "<li>".htmlentities($school['year'])." : ".htmlentities($school['name'])."</li>";
            }
        } else { echo "NO DATA TO SHOW"; }
    ?>
    </p>
    <p>Position:<br/>
    <?php
        if(count($positions) > 0) {
            foreach( $positions as $position ) {
                echo "<li>".htmlentities($position['year'])." : ".htmlentities($position['description'])."</li>";
            }
        } else { echo "NO DATA TO SHOW"; }
    ?>
    </p>
    <a href="index.php">Done</a>
</div>
</html>
