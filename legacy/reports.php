<?php
//smoke and mirrors
    include('inc/head.php');

// login check

    //if not logged in
    if(!isset($_SESSION['id'])) {
        $_SESSION['msg']['error']='Uhm... you\'ve gotta be logged in to do that...';

        header("Location: index.php");
        exit;
    }

//set variables
    $orgid = $_GET['orgid'];
    $usrid = $_SESSION['id'];
    $usr = $_SESSION['usr'];
    $access = $_SESSION['acc'];

    $times = '*';
    $dividedby = '/';
    $plus = '+';
    $minus = '-';

//SQL

    //classroom leaderboard
    $clsldrqry = 'SELECT class.id, class.name, class.numkids, SUM(items.value) AS total, (class.numkids*50) AS goal FROM class LEFT JOIN items ON items.class_id = class.id WHERE items.received = 1 GROUP BY class.name ORDER BY total DESC';
    $clsldrres = mysql_query($clsldrqry);

    $highnum = mysql_fetch_array(mysql_query("SELECT id, numkids FROM class ORDER BY numkids DESC"));
    $highnum = $highnum['numkids'];

    //parent leaderboard
    $prtldrqry = 'SELECT members.fname, members.lname, SUM(items.value) AS total FROM org JOIN members ON org.usr_id = members.id JOIN items ON items.org_id = org.id WHERE items.received = 1 GROUP BY members.usr ORDER BY total DESC';
    $prtldrres = mysql_query($prtldrqry);

    //select YES donate from org table
    $yesqry = 'SELECT * FROM org WHERE donate = 1 ORDER BY name ASC';
    $yesres = mysql_query($yesqry);

    //select NO donate from org table
    $noqry = 'SELECT * FROM org WHERE donate = 2 ORDER BY name ASC';
    $nores = mysql_query($noqry);

    //select proper info from items table
    $rcvqry = 'SELECT items.org_id, items.desc, items.value, items.location, items.itemnum, org.id, org.name FROM items INNER JOIN org ON items.org_id=org.id WHERE received = 1 ORDER BY items.itemnum ASC';
    $rcvres = mysql_query($rcvqry);

//include html header
    include('inc/header.php');
?>
            <div role="main" class="main">

                <section class="page-top">
                    <div class="container">
                        <div class="row">
                            <div class="span12">
                                <ul class="breadcrumb">
                                    <li><a href="index.php">Home</a><span class="divider">/</span></li>
                                    <li class="active">Reports</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="span12">
                                <h2>Krieger Auction Manager    </h2>
                            </div>
                        </div>
                    </div> <!-- /container -->
                </section>

                <div class="container">
                    <div class="row">
                        <div class="span12">
                        <?php
                            //donation goal
                            $goal = 40000;

                            //select total amount donation from items table
                            $solqry = 'SELECT SUM(items.value) AS total FROM items';
                            $solres = mysql_query($solqry);

                            while($solrow = mysql_fetch_array($solres)){
                                $totsol = $solrow['total'];
                                //percentage of goal
                                $persol = floor(($totsol/$goal)*100);
                            }

                            //select total amount donation received from items table
                            $rcvqry2 = 'SELECT SUM(items.value) AS total FROM items WHERE received = 1';
                            $rcvres2 = mysql_query($rcvqry2);

                            while($row = mysql_fetch_array($rcvres2)){
                                $totrcv = $row['total'];
                                //percentage of goal
                                $perrcv = floor(($totrcv/$goal)*100);
                            }

                            //format as currency
                            $totsol = number_format($totsol, 2, '.', ',');
                            $totrcv = number_format($totrcv, 2, '.', ',');
                            $goal = number_format($goal, 2, '.', ',');

                        ?>
                            <div class="progress-label">
                                <span>Solicitation Progress:</span>
                            </div>

                            <div class="progress progress-striped active">
                                <?php
                                    if($persol>100){
                                        $pwidth = 100;
                                    } else {
                                        $pwidth = $persol;
                                    }
                                ?>
                                <div class="bar" data-appear-progress-animation="<?php echo $persol; ?>%" style="width: <?php echo $pwidth; ?>%;">
                                    <span class="pull-right"><?php echo $persol; ?>%</span>
                                </div>
                            </div>

                            <p>
                                <table>
                                    <thead>
                                        <th>Goal</th>
                                        <th>Solicited</th>
                                        <th>Received</th>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>$<?php echo $goal;?></td>
                                            <td>$<?php echo $totsol.' ('.$persol.'%)';?></td>
                                            <td>$<?php echo $totrcv.' ('.$perrcv.'%)';?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </p>
                        </div>

                        <div class="span12">
                            <div class="tabs">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#classldr" data-toggle="tab"><i class="icon-group"></i> Classroom Leaderboard (<?php echo mysql_num_rows($clsldrres);?>)</a></li>
                                    <li><a href="#parentldr" data-toggle="tab"><i class="icon-user"></i> Parent Leaderboard (<?php echo mysql_num_rows($prtldrres);?>)</a></li>
                                    <?php
                                        if($access > 2){
                                            $rcvqry = 'SELECT items.org_id, items.desc, items.value, items.location, items.itemnum, org.id, org.name, org.usr_id FROM items INNER JOIN org ON items.org_id=org.id WHERE received = 1 AND org.usr_id = '.$usrid.' ORDER BY items.itemnum ASC';
                                            $rcvres = mysql_query($rcvqry);
                                        }
                                    ?>
                                    <li><a href="#itemsrcv" data-toggle="tab"><i class="icon-tags"></i> Items Received (<?php echo mysql_num_rows($rcvres);?>)</a></li>
                                    <?php
                                        if($access < 3){
                                    ?>
                                    <li><a href="#donateyes" data-toggle="tab"><i class="icon-thumbs-up"></i> Will Donate (<?php echo mysql_num_rows($yesres);?>)</a></li>
                                    <li><a href="#donateno" data-toggle="tab"><i class="icon-thumbs-down"></i> Will Not Donate (<?php echo mysql_num_rows($nores);?>)</a></li>
                                    <?php
                                        }
                                    ?>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="classldr">

                                        <table class="table table-striped">
                                            <thead>
                                                <th>Classroom</th>
                                                <th>Value Received</th>
                                                <th>Goal</th>
                                                <th>Progress</th>
                                                <th>Weighted Value (Magic #)</th>
                                            </thead>

                                            <tbody>
                                            <?php
                                                while($row = mysql_fetch_array($clsldrres)) {
                                                    echo '<tr>'."\n";
                                                    echo '    <td>'.$row['name'].'</td>'."\n";
                                                    echo '    <td>$'.$row['total'].'</td>'."\n";
                                                    //highest number of kids / number of kids in classroom
                                                    if($row['numkids'] != '0'){

                                                        $magicnum = number_format(eval('return '.$highnum.$dividedby.$row['numkids'].';'), 3, '.', ',');

                                                        //total raised * weighted value above
                                                        $weight = number_format(eval('return '.$row['total'].$times.$magicnum.';'), 2, '.', ',');

                                                        //percentage of goal
                                                        $goal = floor(($row['total']/$row['goal'])*100).'%';

                                                        echo '    <td>$'.$row['goal'].'</td>'."\n";
                                                        echo '    <td>'.$goal.'</td>'."\n";
                                                        echo '    <td>$'.$weight.'&nbsp;(x'.$magicnum.')</td>'."\n";
                                                    } else {
                                                        echo '<td>-</td>'."\n";
                                                        echo '<td>-</td>'."\n";
                                                        echo '<td>-</td>'."\n";
                                                    }
                                                    echo '</tr>'."\n";
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane classldr -->

                                    <div class="tab-pane" id="parentldr">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Name</th>
                                                <th>Received Value</th>
                                                <th>Participation Points</th>
                                            </thead>

                                            <tbody>
                                            <?php
                                                while($row = mysql_fetch_array($prtldrres)) {
                                                    //parent participation points earned
                                                    $points = floor($row['total']/50);

                                                    echo '<tr>'."\n";
                                                    echo '    <td>'.mb_substr($row['fname'], 0, 1, 'utf-8').'.&nbsp;'.$row['lname'].'</td>'."\n";
                                                    echo '    <td>$'.$row['total'].'</td>'."\n";
                                                    echo '    <td><a href="https://creator.zoho.com/dkeiller/krieger-points-2013-2014" target="_blank">'.$points.'</a></td>'."\n";
                                                    echo '</tr>'."\n";
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane parentldr -->

                                    <div class="tab-pane" id="itemsrcv">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Organization</th>
                                                <th>Description</th>
                                                <th>Value</th>
                                                <th>Location</th>
                                                <th>Item #</th>
                                            </thead>

                                            <tbody>
                                            <?php
                                                while($row = mysql_fetch_array($rcvres)) {
                                                    $orgname = (strlen($row['name']) > 23) ? substr($row['name'],0,20).'...' : $row['name'];
													echo '<tr>'."\n";;
													echo '    <td><a href="orginfo.php?orgid='.$row['id'].'" title="'.$row['name'].'">'.$orgname.'</a></td>'."\n";;
                                                    echo '    <td>'.$row['desc'].'</td>'."\n";
                                                    echo '    <td>$'.$row['value'].'</td>'."\n";
                                                    echo '    <td>'.$row['location'].'</td>'."\n";
                                                    echo '    <td>'.$row['itemnum'].'</td>'."\n";
                                                    echo '</tr>'."\n";
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane itemsrcv -->
                                    <?php
                                        if($access < 3){
                                    ?>
                                    <div class="tab-pane" id="donateyes">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Organization</th>
                                                <th>Received?</th>
                                                <th>POC</th>
                                                <th>Phone #</th>
                                                <th>Email</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    while($row = mysql_fetch_array($yesres)) {
                                                        $itmqry = 'SELECT * FROM items WHERE org_id = '.$row['id'].' AND received = 1';
                                                        $itmres = mysql_query($itmqry);
                                                        $items = mysql_num_rows($itmres);
                                                        if($items==0) {
                                                            $rcv = 'No';
                                                        } else {
                                                            $rcv = 'Yes';
                                                        }
                                                        $orgname = (strlen($row['name']) > 23) ? substr($row['name'],0,20).'...' : $row['name'];
                                                        echo '<tr>'."\n";
                                                        echo '    <td><a href="orginfo.php?orgid='.$row['id'].'" title="'.$row['name'].'">'.$orgname.'</a></td>'."\n";
                                                        echo '    <td>'.$rcv.'</td>'."\n";
                                                        echo '    <td>'.$row['poc_name'].'</td>'."\n";
                                                        echo '    <td>'.$row['poc_phone'].'</td>'."\n";
                                                        echo '    <td>'.$row['poc_email'].'</td>'."\n";
                                                        echo '</tr>'."\n";
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane donateyes -->

                                    <div class="tab-pane" id="donateno">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Organization</th>
                                                <th>POC</th>
                                                <th>Phone #</th>
                                                <th>Email</th>
                                            </thead>

                                            <tbody>
                                            <?php
                                                while($row = mysql_fetch_array($nores)) {
                                                    $orgname = (strlen($row['name']) > 23) ? substr($row['name'],0,20).'...' : $row['name'];
                                                    echo '<tr>'."\n";
                                                    echo '    <td><a href="orginfo.php?orgid='.$row['id'].'" title="'.$row['name'].'">'.$orgname.'</a></td>'."\n";
                                                    echo '    <td>'.$row['poc_name'].'</td>'."\n";
                                                    echo '    <td>'.$row['poc_phone'].'</td>'."\n";
                                                    echo '    <td>'.$row['poc_email'].'</td>'."\n";
                                                    echo '</tr>'."\n";
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane donateno -->
                                    <?php
                                        }
                                    ?>

                                </div> <!-- /tab-content -->
                            </div> <!-- /tabs -->

                        </div> <!-- /span12 -->
                    </div> <!-- /row -->
                </div> <!-- /container -->
            </div> <!-- /main -->

<?php
//include html footer
    include('inc/footer.php');
?>