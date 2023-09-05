<?php
// connecting to the MySql database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "maintenance";

$conn = new mysqli($servername, $username, $password, $dbname);

// checking connection:
if ($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
// updating status if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handling checkbox change
    if(isset($_POST['checked']) && !empty($_POST['checked'])) {
        foreach ($_POST['dueFrom'] as $id => $dueFrom) {
            $doneOn = isset($_POST['done_on'][$id]) ? $_POST['done_on'][$id] : null;
            $remarks = isset($_POST['remarks'][$id]) ? $_POST['remarks'][$id] : null;
            $isChecked = isset($_POST['checked'][$id]) ? $_POST['checked'][$id] : 0;
            $dueTill = isset($_POST['dueTill'][$id]) ? $_POST['dueTill'][$id] : null;
            $equipment = isset($_POST['equipment'][$id]) ? $_POST['equipment'][$id] : null;
            $instrCode = isset($_POST['instrCode'][$id]) ? $_POST['instrCode'][$id] : null;
            $frequency = isset($_POST['frequency'][$id]) ? $_POST['frequency'][$id] : null;
            $frequency2 = isset($_POST['frequency2'][$id]) ? $_POST['frequency2'][$id] : null;
            if ($isChecked) {
                $updateQuery2 = "UPDATE pm_schedule SET 
                    DUE_FROM = CASE
                        WHEN FREQ_UNIT = 'WEEKS' 
                        THEN DATE_ADD(DUE_FROM, INTERVAL FREQ_INT WEEK)

                        WHEN FREQ_UNIT = 'MONTHS' 
                        THEN DATE_ADD(DUE_FROM, INTERVAL FREQ_INT MONTH)
                        ELSE DUE_FROM
                    END, 
                    DONE_ON = CURDATE() WHERE SNO = " . $id;
            } else {
                $updateQuery2 = 'UPDATE pm_schedule SET DUE_FROM = "' . $dueFrom . '", 
                    DONE_ON = "' . $doneOn . '" WHERE SNO = ' . $id;
            }            
            $updateQuery1 = 'UPDATE pm_schedule SET EQUIPMENT = "' . $equipment . '",
                MC_CODE = "' . $instrCode . '", FREQ_INT = "' . $frequency . '", FREQ_INT2 = "' . $frequency2 . '" WHERE SNO = ' . $id;
            
            $updateQuery3 = 'UPDATE pm_schedule SET
                DUE_TILL = CASE 
                    WHEN FREQ_UNIT = "WEEKS" 
                    THEN DATE_ADD(DUE_FROM, INTERVAL FREQ_INT2-FREQ_INT WEEK)
                            
                    WHEN FREQ_UNIT = "MONTHS"
                    THEN DATE_ADD(DUE_FROM, INTERVAL FREQ_INT2-FREQ_INT MONTH)
                    ELSE DUE_TILL
                END WHERE SNO = ' . $id;
            
            $conn->query($updateQuery1);
            $conn->query($updateQuery2);
            $conn->query($updateQuery3);
        }
    }

    // handling redirection to different table based on the selected option
    if(isset($_POST['tableSelect'])){
        $selectedTable = $_POST['tableSelect'];
        if($selectedTable == 'calibration') {
            header('Location: cal_ver6.php');
            exit;
        } 
    }     
}
?>
