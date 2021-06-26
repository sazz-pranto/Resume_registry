<?php 
    // user profile validation

    function dataValidation() {
        if(strlen($_POST['first_name']) < 1  || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
    
        $_SESSION['error'] = "All fields are required";
        return false;
        }
        if(strstr($_POST['email'], '@') === false ){
            $_SESSION["error"] = 'Email must have an at-sign (@)' ;
            return false;
        }
        return true;
    }

    // validate position field

    function validatePos() {
        for($i=1; $i<=9; $i++) {
            if ( ! isset($_POST['year'.$i]) ) continue;
            if ( ! isset($_POST['desc'.$i]) ) continue;
        
            $year = $_POST['year'.$i];
            $desc = $_POST['desc'.$i];
        
            if ( strlen($year) == 0 || strlen($desc) == 0 ) {
                $_SESSION['error'] = "All fields are required";
                return false;
            }
        
            if ( ! is_numeric($year) ) {
                $_SESSION['error'] = "Position year must be numeric";
                return false;
            }
        }
            return true;
    }

    // validate education field

    function validateEdu() {
        for($i=1; $i<=9; $i++) {
            if ( ! isset($_POST['edu_year'.$i]) ) continue;
            if ( ! isset($_POST['edu_school'.$i]) ) continue;
        
            $year = $_POST['edu_year'.$i];
            $school = $_POST['edu_school'.$i];
        
            if ( strlen($year) == 0 || strlen($school) == 0 ) {
                $_SESSION['error'] = "All fields are required";
                return false;
            }
        
            if ( ! is_numeric($year) ) {
                $_SESSION['error'] = "Education year must be numeric";
                return false;
            }
        }
            return true;
    }

    //load data from position table
    function loadPos($pdo, $profile_id) {
        $stmt = $pdo->prepare("SELECT * FROM `position` 
                        WHERE `profile_id`=:pid 
                        ORDER BY rank");
        $stmt->execute( array(':pid' => $profile_id ) );
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC); //TODO:check the difference between fetch and fetchAll
        return $positions;
    }

    //load data from education table
    function loadEdu($pdo, $profile_id) {
        $stmt = $pdo->prepare("SELECT `year`, `name` FROM `education` 
                        JOIN `institution` ON education.institution_id = institution.institution_id
                        WHERE `profile_id`=:pid 
                        ORDER BY rank");
        $stmt->execute( array(':pid' => $profile_id ) );
        $educations = $stmt->fetchAll(PDO::FETCH_ASSOC); //TODO:check the difference between fetch and fetchAll
        return $educations;
    }

    // insert data into education table
    function insertEducations($pdo, $profile_id) {
        $rank = 1;            
        for($i=1; $i<=9; $i++) {
            if ( ! isset($_POST['edu_year'.$i]) ) continue;
            if ( ! isset($_POST['edu_school'.$i]) ) continue;

            $year = $_POST['edu_year'.$i];
            $school = $_POST['edu_school'.$i];

            //lookup if the school is in the db
            $institution_id = false;
            $stmt = $pdo->prepare("SELECT `institution_id` FROM `institution`
                        WHERE `name` = :name");
            $stmt->execute( array(':name' => $school ) );
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row !== false) $institution_id = $row['institution_id'];

            //insert the institution if its not recorded previously
            if($institution_id === false) {
                $stmt = $pdo->prepare("INSERT INTO `institution` 
                                (name) VALUES (:name)");
                $stmt->execute( array(':name' => $school ) );
                $institution_id = $pdo->lastInsertId();
            }

            //now its time to insert data into the education table
            $stmt = $pdo->prepare('INSERT INTO `education`
                (`profile_id`, `rank`, `year`, `institution_id`)
                VALUES ( :pid, :rank, :year, :ins_id)');
            $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':ins_id' => $institution_id)
            );
            $rank++;
        }
    }


    // flash messages

    function flashMessages() {
        if ( isset($_SESSION['error']) ) {
            // Look closely at the use of single and double quotes
            echo('<p style="color: red;">'.$_SESSION['error']."</p>\n");
            unset($_SESSION['error']);
        }
        if ( isset($_SESSION['message']) ) {
            // Look closely at the use of single and double quotes
            echo('<p style="color: green;">'.$_SESSION['message']."</p>\n");
            unset($_SESSION['message']);
        }
    }    
