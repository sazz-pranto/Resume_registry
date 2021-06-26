<?php 
    require_once "pdo.php";
    require_once "utilities.php";
    session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Sazzad Hossain's Resume Registry</title>
<!-- bootstrap.php - this is HTML -->

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous">

</head>
<body>
<div class="container">
    <h1>Sazzad Hossain's Resume Registry</h1>
    <?php 
        flashMessages();
    ?>
    <?php if( isset($_SESSION['name']) ) { ?>
        <a href="logout.php">Logout</a>
    <?php } else { ?>
        <p><a href="login.php">Please log in</a></p>
    <?php } ?>
    <?php 
        $stmt = $pdo->query("SELECT * FROM `profile`");
        if($stmt->rowCount() > 0) {
    ?>
    <table border="1">
    <tr>
        <th>Name</th>
        <th>Headline</th>
        <th>Action</th>
    <tr>
    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <tr>
        <td>
            <a href="view.php?profile_id=<?php echo htmlentities($row['profile_id'])?>"><?php echo htmlentities($row['first_name'].' '.$row['last_name'])?></a>
        </td>
        <td>
            <?php echo htmlentities($row['headline']) ?>
        </td>
        <td>
            <?php 
                echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
                echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
            ?>
        </td>
    </tr>
    <?php endwhile ?>
    </table>
        <?php } else { ?>
                <p>No data found</p>
        <?php } ?>
    <a href="add.php">Add New Entry</a>
</div>
</body>
</html>