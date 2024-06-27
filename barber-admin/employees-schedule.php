<?php
    session_start();

    // Page Title
    $pageTitle = 'Meeting Room Schedule';

    // Includes
    include 'connect.php';
    include 'Includes/functions/functions.php'; 
    include 'Includes/templates/header.php';

    // Extra JS FILES
    echo "<script src='https://unpkg.com/sweetalert/dist/sweetalert.min.js'></script>";

    // Check If user is already logged in
    if(isset($_SESSION['username_barbershop_Xw211qAAsq4']) && isset($_SESSION['password_barbershop_Xw211qAAsq4']))
    {
?>
        <!-- Begin Page Content -->
        <div class="container-fluid">
    
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Meeting Room Schedule</h1>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-download fa-sm text-white-50"></i>
                    Generate Report
                </a>
            </div>

            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Meeting Room Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="sb-entity-selector" style="max-width:300px;">
                        <form action="employees-schedule.php" method="POST">
                            <div class="form-group">
                                <label class="control-label" for="employee_schedule_select">
                                    Select Meeting Room to set up the schedule:
                                </label>
                                <div style="display:inline-block;margin-bottom: 10px;">
                                    <?php 
                                        $stmt = $con->prepare('SELECT * FROM meetingroom');
                                        $stmt->execute();
                                        $meetingrooms = $stmt->fetchAll();
                                    
                                        echo "<select class='form-control' name='employee_selected'>";
                                            foreach ($meetingrooms as $room) 
                                            {
                                                echo "<option value='".$room['room_id']."' ".((isset($_POST['employee_selected']) && $_POST['employee_selected'] == $room['room_id']) ? 'selected' : '').">".$room['floor']." ".$room['room']."</option>";
                                            }
                                        echo "</select>";                                    
                                    ?>
                                </div>
                                <button type="submit" name="show_schedule_sbmt" class="btn btn-primary">Show schedule</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="alert alert-info">
                        Configure your week settings here. Just select start time and end time to set up employees working hours.
                    </div>
                    
                    
                    <!-- SCHEDULE PART -->
                    
                    <div class="sb-content" style="min-height: 500px;">
                        <?php

                            /** WHEN SHOW SCHEDULE BUTTON CLICKED **/

                            if(isset($_POST['show_schedule_sbmt']))
                            {
                        ?>
                                <form method="POST" action="employees-schedule.php">
                                    <input type="hidden" name="room_id" value="<?php echo $_POST['employee_selected'];?>" hidden>     
                                    <div class="worktime-days">
                                        <?php
                                            $room_id = $_POST['employee_selected'];
                                            $stmt = $con->prepare('SELECT * FROM meetingroom e, room_schedule es WHERE es.room_id = e.room_id AND e.room_id = ?');
                                            $stmt->execute(array($room_id));
                                            $schedules = $stmt->fetchAll();
            
                                            $days = array("1"=>"Monday",
                                                "2"=>"Tuesday",
                                                "3"=>"Wednesday",
                                                "4"=>"Thursday",
                                                "5"=>"Friday",
                                                "6"=>"Saturday",
                                                "7"=>"Sunday") ;
                                        
                                            // Available days
                                            $av_days = array();
                                            foreach($schedules as $schedule)
                                            {
                                                $av_days[] = $schedule['day_id'];
                                            }
                                        
                                            foreach($days as $key => $value)
                                            {
                                                echo "<div class='worktime-day row'>";
                                                
                                                if(in_array($key, $av_days))
                                                {
                                                    echo "<div class='form-group  col-md-4'>";
                                                        echo "<input name='".$value."' id='".$key."' class='sb-worktime-day-switch' type='checkbox' checked>";
                                                        echo "<span class='day-name'>";                
                                                            echo $value;
                                                        echo "</span>";
                                                    echo "</div>";
                                                    
                                                    foreach($schedules as $schedule)
                                                    {
                                                        if($schedule['day_id'] == $key)
                                                        {
                                                            echo "<div class='time_ col-md-8 row'>";
                                                            echo "<div class='form-group col-md-6'>";
                                                            echo "<input type='time' name='".$value."-from' value='".$schedule['from_hour']."' class='form-control'>";
                                                            echo "</div>";
                                                            echo "<div class='form-group col-md-6'>";
                                                            echo "<input type='time' name='".$value."-to' value='".$schedule['to_hour']."'  class='form-control'>";
                                                            echo "</div>";
                                                            echo "</div>";
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    echo "<div class='form-group  col-md-4'>";
                                                    echo "<input name='".$value."' id='".$key."' class='sb-worktime-day-switch' type='checkbox'>";
                                                    echo "<span class='day-name'>";                
                                                    echo $value;
                                                    echo "</span>";
                                                    echo "</div>";
                                                    
                                                    echo "<div class='time_ col-md-8 row' style='display:none;'>";
                                                    echo "<div class='form-group col-md-6'>";
                                                    echo "<input type='time' name='".$value."-from' value='09:00' class='form-control'>";
                                                    echo "</div>";
                                                    echo "<div class='form-group col-md-6'>";
                                                    echo "<input type='time' name='".$value."-to' value='18:00' class='form-control'>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                                
                                                echo "</div>";
                                            }
                                        ?>
                                    </div>

                                    <!-- SAVE SCHEDULE BUTTON -->

                                    <div class="form-group">
                                        <button type="submit" name="save_schedule_sbmt" class="btn btn-info">Save schedule</button>
                                    </div>
                                </form>
                        <?php
                            }
                        ?>
                    </div>

                    <?php

                        /** WHEN SAVE SCHEDULE BUTTON CLICKED **/

                        if(isset($_POST['save_schedule_sbmt']))
                        {
                            $days = array("1"=>"Monday",
                               "2"=>"Tuesday",
                               "3"=>"Wednesday",
                               "4"=>"Thursday",
                               "5"=>"Friday",
                               "6"=>"Saturday",
                               "7"=>"Sunday") ;
                            $stmt = $con->prepare("DELETE FROM room_schedule WHERE room_id = ?");
                            $stmt->execute(array($_POST['room_id']));
                            
                            foreach($days as $key => $value)
                            {
                                if(isset($_POST[$value]))
                                {   
                                    $stmt = $con->prepare("INSERT INTO room_schedule(room_id,day_id,from_hour,to_hour) VALUES(?, ?, ?, ?)");
                                    $stmt->execute(array($_POST['room_id'], $key, $_POST[$value.'-from'], $_POST[$value.'-to']));
                                    
                                    $message = "You have successfully updated meeting room schedule!";
                                    
                                    ?>

                                        <script type="text/javascript">
                                            swal("Set Meeting Room Schedule", "You have successfully set the meeting room schedule!", "success").then((value) => {}); 
                                        </script>

                                    <?php
                                }
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
  
<?php 
        
        // Include Footer
        include 'Includes/templates/footer.php';
    }
    else
    {
        header('Location: login.php');
        exit();
    }

?>
