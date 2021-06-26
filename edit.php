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

    // updates data in profile table
    if( isset($_POST['update']) ) {
        // Data validation
        
        if(dataValidation() === false || validatePos() === false) {
            header('location: edit.php?profile_id='.$_REQUEST['profile_id']);
            return;
        } else {
            try {
                // updating data to profile table
            $sql = "UPDATE `profile` SET `first_name` = :fn,
            `last_name` = :ln, `email` = :em, `headline` = :he, `summary` = :su
            WHERE `profile_id` = :profile_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':fn'         => $_POST['first_name'],
                ':ln'         => $_POST['last_name'],
                ':em'         => $_POST['email'],
                ':he'         => $_POST['headline'],
                ':su'         => $_POST['summary'],
                ':profile_id' => $_GET['profile_id'])
                );

            // updating data to position table
            // First, clear out the old position entries (only needed to add the new entries)
            $stmt = $pdo->prepare('DELETE FROM `position` WHERE `profile_id`=:pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

            // Insert the position entries
            $rank = 1;
            for($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;

                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];
                $stmt = $pdo->prepare('INSERT INTO `position`
                        (`profile_id`, `rank`, `year`, `description`)
                        VALUES ( :pid, :rank, :year, :desc)');

                $stmt->execute(array(
                ':pid' => $_REQUEST['profile_id'],
                ':rank' => $rank,
                ':year' => $year,
                ':desc' => $desc)
                );
                $rank++;
            }

            // updating data to eduction table
            // First, clear out the old education entries (only needed to add the new entries)
            $stmt = $pdo->prepare('DELETE FROM `education` WHERE `profile_id`=:pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

            //insert the new education entries
            insertEducations($pdo, $_REQUEST['profile_id']);

            $_SESSION['message'] = 'Profile updated';
            header( 'Location: index.php' );
            return;
            } catch (Exception $ex ) { 
                echo("Internal error, please contact support");
                error_log("edit.php(JS_w1), error=".$ex->getMessage());
            }
        }
    }

    // redirect back to index.php in cancelled
    if(isset($_POST['cancel'])) {
        header('Location: index.php');
        return;
    }
?>
<!DOCTYPE html>
<html>
<head>
<title>Sazzad Hossain's Profile Edit</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css"> 

<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

</head>
<body>
<div class="container">
    <h1>Editing Profile for <?php echo htmlentities($_SESSION['name']) ?></h1>
<?php
    flashMessages();

    //fetching data from profile table
    $stmt = $pdo->prepare("SELECT * FROM `profile` WHERE `profile_id`=:pid AND `user_id`=:uid");
    $stmt->execute( array(
        ':pid' => $_GET['profile_id'],
        ':uid' => $_SESSION['user_id']
    ) );
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<p>NO DATA TO SHOW!</p>";
    }

    //fetching data from position table
    $stmt_pos = $pdo->prepare("SELECT `year`, `description` FROM `position` WHERE `profile_id`=:pid");
    $stmt_pos->execute( array(':pid' => $_GET['profile_id'] ) );

    //load up the education rows
    $schools = loadEdu($pdo, $_GET['profile_id']);
?>
    <form method="post">
        <p>First Name:
        <input type="text" name="first_name" size="60" value="<?php echo htmlentities($row['first_name']) ?>"/></p>
        <p>Last Name:
        <input type="text" name="last_name" size="60" value="<?php echo htmlentities($row['last_name']) ?>"/></p>
        <p>Email:
        <input type="text" name="email" size="30" value="<?php echo htmlentities($row['email']) ?>"/></p>
        <p>Headline:<br/>
        <input type="text" name="headline" size="80" value="<?php echo htmlentities($row['headline']) ?>"/></p>
        <p>Summary:<br/>
        <textarea name="summary" rows="8" cols="80"> <?php echo htmlentities($row['summary']) ?> </textarea>

        <!-- different syntax has been used on education and position fields to understand the use of it -->
        <p>
        Education: <input type="submit" id="addEdu" value="+">
            <div id="edu_fields">
                <?php if( count($schools) > 0 ) {
                    $countEdu = 0;
                    foreach( $schools as $school ) {
                        $countEdu++; //keeps track of the number of fields

                        //this div will show up if education table contains any wors
                        echo '<div id="edu'.$countEdu.'">
                            <p>
                                Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" />
                                <input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;" />
                            </p>
                            <p>
                                School: <input type="text" size="80" name="edu_school'.$countEdu.'" value="'.htmlentities($school['name']).'" class="school" />
                            </p>
                        </div>';
                    }
                } else { echo '<p>NO DATA TO SHOW!</p>'; } ?>
            </div>
        </p>

        <p>
        Position: <input type="submit" id="addPos" value="+">
            <div id="position_fields">
                <?php if($stmt_pos->rowCount() > 0) { $rowNum = 0; //keeps track of the position number  ?>
                    <!-- this block prints out some of the data from position table -->
                    <?php while($row_pos = $stmt_pos->fetch(PDO::FETCH_ASSOC)) { $rowNum++; ?>
                    <div id="position<?php echo $rowNum ?>">
                        <p>Year: <input type="text" name="year<?php echo $rowNum ?>" value="<?php echo $row_pos['year'] ?>" /> 
                        <input type="button" value="-" 
                            onclick="$('#position<?php echo $rowNum ?>').remove();return false;"></p> 
                        <textarea name="desc<?php echo $rowNum ?>" rows="8" cols="80"><?php echo htmlentities($row_pos['description']) ?></textarea>
                    </div>
                    <?php 
                        $countPos = $rowNum;  //to keep track of position number to add new position fields
                    } 
                    ?>

                <?php } else { echo "<p>NO DATA TO SHOW!</p>"; } ?>
            </div>
        </p>

        <p>
        <input type="submit" value="Save" name="update">
        <input type="submit" name="cancel" value="Cancel">
        </p>
    </form>
</div>

<!-- <script type="text/javascript" src="jquery.min.js"></script> -->
<script type="text/javascript">
    countPos = <?= $countPos ?>;
    countEdu = <?= $countEdu ?>;

    // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function(){
        window.console && console.log('Document ready called');

        //jQuery for position fields
        $('#addPos').click(function(event){
            // http://api.jquery.com/event.preventdefault/
            event.preventDefault();
            if ( countPos >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position "+countPos);
            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
                <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                <input type="button" value="-" \
                    onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
                <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
        });

        // jQuery for education fields
        $('#addEdu').click(function(event){
            event.preventDefault();
            if ( countEdu >= 9 ) {
                alert("Maximum of nine education entries exceeded");
                return;
            }
            countEdu++;
            window.console && console.log("Adding education "+countEdu);

            //dadu created a template that has much easier syntax for this part, watch js_w4_7 22:41 for details
            $('#edu_fields').append(
                '<div id="edu'+countEdu+'"> \
                <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
                <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
                <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
                </p></div>'
            );
            $('.school').autocomplete({ source: "school.php" });
        });
    });
</script>
</body>
</html>
