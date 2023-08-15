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
        $sql = "UPDATE pm_schedule SET DONE_ON = CURDATE(), IS_DONE = true WHERE SNO = $SNo";
        $conn->query($sql);
    
            // if ($conn->query($sql) === TRUE) {
            //     echo "<script type='text/javascript'>alert('Status updated successfully');</script>";
            // } else {
            //     echo "Error updating record: " . $conn->error;
            // }
    }

    if ($action == 'reset') {
        $SNo = $_POST["SNo"];
        $sql = "UPDATE pm_schedule SET DONE_ON = NULL, IS_DONE = false WHERE SNO = $SNo";
        $conn->query($sql);
    
            // if ($conn->query($sql) === TRUE) {
            //     echo "<script type='text/javascript'>alert('Status reset successfully');</script>";
            // } else {
            //     echo "Error resetting record: " . $conn->error;
            // }
    }

    if ($action == 'submit') {
    $sql = "UPDATE pm_schedule SET 
            DUE_DATE = CASE 
                WHEN FREQ_UNIT = 'WEEKS' THEN DATE_ADD(DUE_DATE, INTERVAL FREQ_INT WEEK)
                WHEN FREQ_UNIT = 'MONTHS' THEN DATE_ADD(DUE_DATE, INTERVAL FREQ_INT MONTH)
                ELSE DUE_DATE
            END,
            NEXT_DUE = CASE 
                WHEN FREQ_UNIT = 'WEEKS' THEN DATE_ADD(NEXT_DUE, INTERVAL FREQ_INT WEEK)
                WHEN FREQ_UNIT = 'MONTHS' THEN DATE_ADD(NEXT_DUE, INTERVAL FREQ_INT MONTH)
                ELSE NEXT_DUE
            END,
            IS_DONE = false 
            WHERE IS_DONE = true";
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
        if($selectedTable == 'calibration') {
            header('Location: Calibration_update.php');
            exit;
        } 
    }  
    
}

// Retrieve data from table
$sql = "SELECT * FROM pm_schedule";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Maintenance Status</title>
        
        <link rel="stylesheet" type="text/css" href="styles.css">
        
        <script type="text/javascript">
            function confirmUpdate() {
                alert("Status updated successfully");
                return true;
            }

            function confirmReset() {
                alert("Status reset successfully");
                return true;
            }
        </script>
    </head>
    <body>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class='top-container'>
                <select name="tableSelect" onchange="this.form.submit()" class='action-form2'>
                    <option value="maintenance" selected>Maintenance</option>
                    <option value="calibration">Calibration</option>
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

        <h1>Machine Maintenance</h1>
        <table>
            <thead>
                <tr>
                    <th style='width:2.5%'>S.N.</th>
                    <th style='width:30%'>Equipment</th>
                    <th style='width:7%'>MC_Code</th>
                    <th style='width:7%'>Model/Sr.no.</th>
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
                        echo "<td>" . $row["SNO"] . "</td>";
                        echo "<td>" . $row["EQUIPMENT"] . "</td>";
                        echo "<td>" . $row["MC_CODE"] . "</td>";
                        echo "<td>" . $row["MODEL/SR_NO"] . "</td>";
                      
                        //frequency logic:
                        if ($row["FREQ_UNIT"] == "WEEKS") {
                            echo "<td>" . $row["FREQ_INT"] . "-" . ($row["FREQ_INT"] + $row["PERIOD"]) . " weeks</td>";
                        } else {
                            echo "<td>" . $row["FREQ_INT"] . "-" . ($row["FREQ_INT"] + $row["PERIOD"]) . " months</td>";
                        } 
                        echo "<td>" . $row["DUE_DATE"] . "</td>";
                        echo "<td>" . $row["DONE_ON"] . "</td>";
                        
                        // displaying status in the table
                        $currDate = new DateTime();
                        $lastDue = new DateTime($row["DUE_DATE"]);
                        $nextDue = new DateTime($row["NEXT_DUE"]);
                        // checking if curr date is within maintenance period
                        if($currDate >= $lastDue && $currDate <= $nextDue){
                            // checking if done
                            if($row["IS_DONE"] == true){
                                $status = 'DONE';
                            } else {
                                $dueInterval = $lastDue->diff($currDate);
                                $period = $row["PERIOD"];
                                $freqUnit = $row["FREQ_UNIT"];
                                if($freqUnit == "WEEKS") {
                                    if($dueInterval->days <= $period*7){
                                        $status = 'PENDING';
                                    } else {
                                        $status = 'OVERDUE';
                                    }
                                } else {
                                    if($dueInterval->m <= $period){
                                        $status = 'PENDING';
                                    } else {
                                        $status = 'OVERDUE';
                                    }
                                }                              
                            }
                        } else {
                            $status = 'NOT DUE';
                        }
                        echo "<td class='$status'>" . $status . "</td>";

                        echo "<td>";
                        // Enable/disable buttons based on maintenance period
                        $disabled = "";
                        if ($status == 'NOT DUE') {
                            $disabled = "disabled";
                        }                        
                        echo "<form method='POST' class='action-form'>";
                        echo "<input type='hidden' name='SNo' value='" . $row["SNO"] . "'/>";
                        echo "<input type='hidden' name='action' value='update'/>";
                        echo "<button type='submit' " . $disabled . ">Update</button>";
                        echo "</form>";

                        echo "<form method='POST' class='action-form'>";
                        echo "<input type='hidden' name='SNo' value='" . $row["SNO"] . "'/>";
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
