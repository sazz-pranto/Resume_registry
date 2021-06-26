<?php 
    require_once "pdo.php";
    require_once "utilities.php";
    session_start();
    if ( !isset($_SESSION['name']) ) {
        die('ACCESS DENIED');
    }
    if ( isset($_POST['cancel']) ) {
        header('Location: index.php');
        return;
    }

    if(isset($_POST['add'])) {
        if(dataValidation() === false || validatePos() === false || validateEdu() === false) {
            header('location: add.php');
            return;
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO `profile`
                (`user_id`, `first_name`, `last_name`, `email`, `headline`, `summary`)
                VALUES ( :usid, :fn, :ln, :em, :he, :su)');

                $stmt->execute(array(
                ':usid' => $_SESSION['user_id'],
                ':fn'   => $_POST['first_name'],
                ':ln'   => $_POST['last_name'],
                ':em'   => $_POST['email'],
                ':he'   => $_POST['headline'],
                ':su'   => $_POST['summary'])
                );
                
                $profile_id = $pdo->lastInsertId();
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
                    ':pid' => $profile_id,
                    ':rank' => $rank,
                    ':year' => $year,
                    ':desc' => $desc)
                    );
                    $rank++;
                }

                insertEducations($pdo, $profile_id); //inserts data into education table

                $_SESSION['message'] = "Profile added";
                header('Location: index.php');
                return;
            } catch (Exception $ex ) { 
                echo("Internal error, please contact support");
                error_log("add.php(JS_w3), error=".$ex->getMessage());
            }
        }
    }

?>
<!DOCTYPE html>
<html>
<head>
<title>Sazzad Hossain's Profile Add</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css"> 

<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

</head>

<body>
<div class="container">
    <h1>Adding Profile for <?php echo htmlentities($_SESSION['name']) ?></h1>
    <?php
        flashMessages();
    ?>
    <form method="post">
        <p>First Name:
        <input type="text" name="first_name" size="60"/></p>
        <p>Last Name:
        <input type="text" name="last_name" size="60"/></p>
        <p>Email:
        <input type="text" name="email" size="30"/></p>
        <p>Headline:<br/>
        <input type="text" name="headline" size="80"/></p>
        <p>Summary:<br/>
        <textarea name="summary" rows="8" cols="80"></textarea>
        </p>
        <p>
        Education: <input type="submit" id="addEdu" value="+">
        <div id="edu_fields">
        </div>
        </p>
        <p>
        Position: <input type="submit" id="addPos" value="+">
        <div id="position_fields">
        </div>
        </p>
        <p>
        <input type="submit" value="Add" name="add">
        <input type="submit" name="cancel" value="Cancel">
        </p>
    </form>
</div>
<!-- <script type="text/javascript" src="jquery.min.js"></script> -->
<script type="text/javascript">
    countPos = 0;
    countEdu = 0;

    // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function(){
        window.console && console.log('Document ready called');
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
