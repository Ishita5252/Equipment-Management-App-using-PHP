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
        foreach ($_POST['lastDone'] as $id => $lastDone) {
            $doneOn = isset($_POST['done_on'][$id]) ? $_POST['done_on'][$id] : null;
            $remarks = isset($_POST['remarks'][$id]) ? $_POST['remarks'][$id] : null;
            $isChecked = isset($_POST['checked'][$id]) ? $_POST['checked'][$id] : 0;
            $nextDue = isset($_POST['nextDue'][$id]) ? $_POST['nextDue'][$id] : null;
            $equipment = isset($_POST['equipment'][$id]) ? $_POST['equipment'][$id] : null;
            $instrCode = isset($_POST['instrCode'][$id]) ? $_POST['instrCode'][$id] : null;
            $frequency = isset($_POST['frequency'][$id]) ? $_POST['frequency'][$id] : null;
            if ($isChecked) {
                $updateQuery = 'UPDATE calibration SET INSTR_NAME = "' . $equipment . '",
                INSTR_CODE = "' . $instrCode . '", FREQ = "' . $frequency . '", 
                DONE_ON = CURDATE(), LAST_DONE = DONE_ON, 
                NEXT_DUE = DATE_ADD(DONE_ON, INTERVAL FREQ MONTH), 
                REMARKS = "' . $remarks . '" WHERE SNo = ' . $id;
            } else {
                $updateQuery = 'UPDATE calibration SET INSTR_NAME = "' . $equipment . '", 
                INSTR_CODE = "' . $instrCode . '", FREQ = "' . $frequency . '", 
                LAST_DONE = "' . $lastDone . '", NEXT_DUE = DATE_ADD(LAST_DONE, INTERVAL FREQ MONTH), 
                DONE_ON = "' . $doneOn . '", 
                REMARKS = "' . $remarks . '" WHERE SNo = ' . $id;
            }            
            $conn->query($updateQuery);
        }
    }

    // handling redirection to different table based on the selected option
    if(isset($_POST['tableSelect'])){
        $selectedTable = $_POST['tableSelect'];
        if($selectedTable == 'maintenance') {
            header('Location: pm_ver6.php');
            exit;
        } 
    }
}
?>
