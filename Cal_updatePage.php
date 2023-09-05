<?php
require_once('cal_ver6_operations.php');
require_once('callog_data_ver2.php');
require 'vendor/autoload.php';

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

// // updating status if form is submitted (file: cal_operations.php)

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
        <div class="top-container">            
            <!-- adding Drop-down for table selection -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="float: right;">
                <select name="tableSelect" onchange="this.form.submit()" class="action-form2">
                    <option value="calibration" selected>Calibration</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </form>         
            <!-- adding 'Log Data' button -->
            <form action="callog_data_ver2.php" method="post" style="float: right;">
                <input type="hidden" name="log_data" value="1">
                <input type="submit" value="Log Data" class="action-form2">
            </form>
            <!-- adding 'Update Schedule' button
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="float: right;">
                <input type="submit" name="update_schedule" value="Update Schedule" class="action-form2">
            </form> -->
            <!-- adding Logout button -->
            <form method="POST" action="logout.php" style="float: right;">
                <button type="submit" class="action-form2">Logout</button>
            </form>
            <h1>Machine Calibration</h1>
        </div>

        

        <!-- adding 'Save Changes' button -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="submit" value="Save Changes" class="action-form2">

        <table>
            <thead>
                <tr>
                    <th style='width:2%'>S.N.</th>
                    <th style='width:15%'>Equipment</th>
                    <th style='width:15%'>Instrument Code</th>
                    <th style='width:5%'>Frequency</th>
                    <th style='width:10%'>Last Done</th>
                    <th style='width:10%'>Next due</th>
                    <th style='width:10%'>Done on</th>
                    <th style='width:2.5%'>Done</th>
                    <th style='width:5%'>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["SNo"] . "</td>";
                        echo "<td><input type='text' name='equipment[" . $row["SNo"] . "]' value='" . $row["INSTR_NAME"] . "'></td>";
                        echo "<td><input type='text' name='instrCode[" . $row["SNo"] . "]' value='" . $row["INSTR_CODE"] . "' style='width: 80%;'></td>";
                        echo "<td><input type='number' name='frequency[" . $row["SNo"] . "]' value='" . $row["FREQ"] . "' style='width: 40%;'> Mo</td>";
                        echo "<td><input type='date' name='lastDone[" . $row["SNo"] . "]' value='" . $row["LAST_DONE"] . "'></td>";
                        echo "<td><input type='date' name='nextDue[" . $row["SNo"] . "]' value='" . $row["NEXT_DUE"] . "'></td>";
                        echo "<td><input type='date' name='done_on[" . $row["SNo"] . "]' value='" . $row["DONE_ON"] . "'></td>";

                        // calculating status
                        $currDate = new DateTime();
                        $nextDueDate = new DateTime($row["NEXT_DUE"]);
                        $dueInterval = $currDate->diff($nextDueDate);
                        //print ($dueInterval->days); print (" ");
                        //$isDone = $row["IS_DONE"];
                        $doneOnDate = $row["DONE_ON"];
                        // checking if curr date is within maintenance period                        
                        if ($doneOnDate == "0000-00-00" || $doneOnDate == null) {
                            if ($currDate > $nextDueDate) {
                                $status = 'OVERDUE';
                            } else {
                                if($dueInterval->days < 10){
                                    $status = 'PENDING';
                                }
                                else
                                    $status = 'NOT DUE';
                            }
                        } else {
                            $status = 'NOT DUE';
                        }

                        echo "<input type='hidden' name='checked[" . $row["SNo"] . "]' value='0'>";
                        echo "<td><input type='checkbox' name='checked[" . $row["SNo"] . "]' value='1' "
                         . ($status == 'NOT DUE' ? " disabled" : "") . "></td>";                        
                        
                        echo "<td class='$status'>" . $status . "</td>";
                        
                        echo "<td><input type='text' name='remarks[" . $row["SNo"] . "]' value='" . $row["REMARKS"] . "'></td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </form>
    </body>
</html>

<?php
// close database connection
$conn->close();
?>
