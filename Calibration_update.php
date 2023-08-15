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
    $action = $_POST["action"];

    if ($action == 'update') {        
        $SNo = $_POST['SNo'];             
        $sql = "UPDATE calibration SET DONE_ON = CURDATE(), IS_DONE = true WHERE SNo = $SNo";
        $conn->query($sql);
    }

    if ($action == 'reset') {
        $SNo = $_POST["SNo"];
        $sql = "UPDATE calibration SET DONE_ON = NULL, IS_DONE = false WHERE SNo = $SNo";
        $conn->query($sql);
    }

    if ($action == 'submit') {
    $sql = "UPDATE calibration SET LAST_DUE = DATE_ADD(DONE_ON, INTERVAL FREQ_MONTHS MONTH), NEXT_DUE = DATE_ADD(LAST_DUE, INTERVAL FREQ_MONTHS MONTH), IS_DONE = false WHERE IS_DONE = true";
    $conn->query($sql);

        // if ($conn->query($sql) === TRUE) {
        //     echo "<script type='text/javascript'>alert('Submitted successfully');</script>";
        // } else {
        //     echo "Error submitting record: " . $conn->error;
        // }
    }

    // handling redirection to different table based on the selected option
    if(isset($_POST['tableSelect'])){
        $selectedTable = $_POST['tableSelect'];
        if($selectedTable == 'maintenance') {
            header('Location: Maintenance_update.php');
            exit;
        } 
    }  
    
}

// Retrieve data from table
$sql = "SELECT * FROM calibration";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Calibration Status</title>
        
        <link rel="stylesheet" type="text/css" href="styles.css">
        
    </head>
    <body>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="top-container">
                <select name="tableSelect" onchange="this.form.submit()" class='action-form2'>
                    <option value="calibration" selected>Calibration</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                <form method="POST" style="float: right;">
                    <input type="hidden" name="action" value="submit"/>
                    <button type="submit" class='action-form2'>Submit and Log</button>
                </form>

                <form method="POST" action="logout.php" style="float: right;">
                    <button type="submit" class='action-form2'>Logout</button>
                </form>
            </div>
        </form>

        <h1>Machine Calibration</h1>
        <table>
            <thead>
                <tr>
                    <th style='width:2.5%'>S.N.</th>
                    <th style='width:17%'>Equipment</th>
                    <th style='width:17%'>Internal M/C</th>
                    <th style='width:10%'>Instrument Code</th>
                    <!--<th>Ref. Std.</th>-->
                    <th style='width:7%'>Frequency</th>
                    <th style='width:7%'>Due date</th>
                    <!-- <th>Next due</th> -->
                    <th style='width:7%'>Done on</th>
                    <th style='width:7%'>Status</th>
                    <th>Ation</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["SNo"] . "</td>";
                        echo "<td>" . $row["INSTR_NAME"] . "</td>";
                        echo "<td>" . $row["INTERNAL_MC"] . "</td>";
                        echo "<td>" . $row["INSTR_CODE"] . "</td>";
                        //echo "<td>" . $row["REF_STD"] . "</td>";
                        echo "<td>" . $row["FREQ_MONTHS"] . " months</td>";
                        echo "<td>" . $row["LAST_DUE"] . "</td>";
                        //echo "<td>" . $row["NEXT_DUE"] . "</td>";
                        echo "<td>" . $row["DONE_ON"] . "</td>";
                        
                        // displaying status in the table
                        #echo "<td>" . $row["STATUS"] . "</td>";
                        $currDate = new DateTime();
                        $dueDate = new DateTime($row["LAST_DUE"]);
                        $dueInterval = $currDate->diff($dueDate);
                        // checking if curr date is within maintenance period
                        if ($currDate <= $dueDate && $dueInterval->days > 10) {
                            $status = 'NOT DUE';
                        } else {
                            if ($row["IS_DONE"] == true) {
                                $status = 'DONE';
                            } else {
                                if ($currDate < $dueDate->modify('+1 day')) {
                                    $status = 'PENDING';
                                } else {
                                    $status = 'OVERDUE';
                                }
                            }
                        }
                        echo "<td class='$status'>" . $status . "</td>";

                        echo "<td>";
                        // Enable/disable buttons based on maintenance period
                        $disabled = "";
                        if ($status == 'NOT DUE') {
                            $disabled = "disabled";
                        }                        
                        echo "<form method='POST' class='action-form'>";
                        echo "<input type='hidden' name='SNo' value='" . $row["SNo"] . "'/>";
                        echo "<input type='hidden' name='action' value='update'/>";
                        echo "<button type='submit' " . $disabled . ">Update</button>";
                        echo "</form>";

                        echo "<form method='POST' class='action-form'>";
                        echo "<input type='hidden' name='SNo' value='" . $row["SNo"] . "'/>";
                        echo "<input type='hidden' name='action' value='reset'/>";
                        echo "<button type='submit' " . $disabled . ">Reset</button>";
                        echo "</form>";

                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <!-- <form method="POST">
            <input type="hidden" name="action" value="submit"/>
            <button type="submit" class="submit-btn">Submit and Log</button>
        </form> -->
    </body>
</html>

<?php
// close database connection
$conn->close();
?>
