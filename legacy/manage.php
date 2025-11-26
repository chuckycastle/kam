<?php
//smoke and mirrors
    include('inc/head.php');

// LOGIN/PAGE ACCESS CHECK

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

//POST

    //if submit add
    if($_POST['submit']=='Add') {
        $name = val($_POST['name']);
        $catid = val($_POST['catid']);
        if($name==''){
            $_SESSION['msg']['error']='Oh snap! You forgot to enter an organization name!';
        } elseif($catid=='0'){
            $_SESSION['msg']['error']='Oh snap! You forgot to enter a category!';
        } else {
            $url = val($_POST['url']);
            $desc = val($_POST['desc']);
            $poc_name = val($_POST['poc_name']);
            $poc_title = val($_POST['poc_title']);
            $poc_email = val($_POST['poc_email']);
            $poc_phone = val(preg_replace("/[^0-9]/","", $_POST['poc_phone']));
            $addedby = $usrid;

            $sql = 'INSERT INTO org (id, name, cat_id, url, avail, org.desc, poc_name, poc_title, poc_email, poc_phone, dt, addedby) VALUES (NULL, "'.nameize($name).'", "'.$catid.'", "'.$url.'", 1, "'.sentence_case($desc).'", "'.nameize($poc_name).'", "'.$poc_title.'", "'.$poc_email.'", "'.formatphone($poc_phone).'", now(), "'.$addedby.'")';
            $res = mysql_query($sql);

            $_SESSION['msg']['success']='Sweet! New organization added!';
        }
    }

    //if submit markread
    if(isset($_POST['mark'])) {
        $id = $_POST['mark'];

        $sql = 'UPDATE msg SET new = 1 WHERE id = '.$id;
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Sweet!  Notification marked as read!';
    }

    //if submit delmsg
    if(isset($_POST['del'])) {
        $id = $_POST['del'];

        $sql = 'DELETE FROM msg WHERE id = '.$id;
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Buh-Bye! Notification deleted!';
    }

    //if submit send
    if($_POST['submit']=='Send') {
        $usr_id = val($_POST['usr_id']);
        $text = val($_POST['text']);
        $from = 'SELECT fname, lname FROM members WHERE members.id = '.$usrid.' LIMIT 1';
        $from = mysql_query($from);
        $from = mysql_fetch_array($from);
        $sql = 'INSERT INTO msg (usr_id, text, dt) VALUES ("'.$usr_id.'", "From '.mb_substr($from['fname'], 0, 1, 'utf-8').'.&nbsp;'.$from['lname'].': '.sentence_case($text).'", now())';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Lookie you!  Message sent!';
    }

    //if submit updusr
    if($_POST['submit']=='Update') {
        $id = val($_POST['usrid']);
        $fname = val($_POST['fname']);
        $lname = val($_POST['lname']);
        $email = val($_POST['email']);
        $opt_out = val($_POST['opt_out']);

        $updqry = 'SELECT * FROM members WHERE id = '.$id;
        $updres = mysql_query($updqry);
        $user = mysql_fetch_array($updres);

        if(!isset($_POST['acc'])){
            $acc = $user['acc'];
        } else {
            $acc = val($_POST['acc']);
        }
        if(!isset($_POST['classid'])){
            $class_id = $user['class_id'];
        } else {
            $class_id = val($_POST['classid']);
        }
        if(!isset($_POST['classid2'])){
            $class_id2 = $user['class_id2'];
        } else {
            $class_id2 = val($_POST['classid2']);
        }
        if(!isset($_POST['assign'])){
            $assign = $user['assign'];
        } else {
            $assign = val($_POST['assign']);
        }

        mysql_query('UPDATE members SET fname="'.nameize($fname).'", lname="'.nameize($lname).'", email="'.$email.'", acc="'.$acc.'", assign="'.$assign.'", class_id = "'.$class_id.'", class_id2 = "'.$class_id2.'", opt_out = "'.$opt_out.'" WHERE members.id='.$id.' LIMIT 1');
        $_SESSION['msg']['success']='Right on, user information successfully updated!';

        if(strlen($_POST['newpw'])>0){
            if(strlen($_POST['verpw'])<1){
                $_SESSION['msg']['error']='Oops!  You didn\'t verify your new password!';
            } else {
                if(strlen($_POST['password'])<1){
                    $_SESSION['msg']['error']='Uh oh, you didn\'t enter your current password.';
                } else {
                    if(md5($_POST['password'])==$user['pass']){
                        if($_POST['verpw']==$_POST['newpw']){
                            mysql_query('UPDATE members SET pass = "'.md5($_POST['verpw']).'" WHERE members.id = '.$id.' LIMIT 1');
                            $_SESSION['msg']['success']='Woo hoo!  Your password was successfully updated!';
                        } else {
                            $_SESSION['msg']['error']='Uh oh, your passwords don\'t match.';
                        }
                    } else {
                        $_SESSION['msg']['error']='Uh oh, you entered an invalid current password.';
                    }
                }
            }
        }
    }

    //if submit delusr
    if($_POST['submit']=='Delete') {
        //delete account
        mysql_query('DELETE FROM members WHERE id = '.$_POST['usrid'].' LIMIT 1');
        //reset all sub accounts to 0
        mysql_query('UPDATE members SET sub = 0 WHERE sub = '.$_POST['usrid']);
        //reset class parents
        mysql_query('UPDATE class SET parent1 = 0 WHERE parent1 = '.$_POST['usrid']);
        mysql_query('UPDATE class SET parent2 = 0 WHERE parent2 = '.$_POST['usrid']);
        //set notes/items/msg/org to unassigned
        mysql_query('UPDATE notes SET usr_id = "37" WHERE usr_id = '.$_POST['usrid']);
        mysql_query('UPDATE items SET usr_id = "37" WHERE usr_id = '.$_POST['usrid']);
        mysql_query('UPDATE msg SET usr_id = "37" WHERE usr_id = '.$_POST['usrid']);
        mysql_query('UPDATE org SET usr_id = "37" WHERE usr_id = '.$_POST['usrid']);

        $_SESSION['msg']['info']='Right on!  You successfully deleted the user!';
    }

//SQL

    //select proper info from msg table
    $msgqry = 'SELECT * FROM msg WHERE usr_id = '.$usrid.' ORDER BY dt ASC';
    $msgres = mysql_query($msgqry);

    //select orgs assigned to me
    $mineqry = 'SELECT * FROM org WHERE usr_id = '.$usrid.' ORDER BY name ASC';
    $mineres = mysql_query($mineqry);

    //select proper info from org table
    $orgqry = 'SELECT * FROM org WHERE avail = 0 ORDER BY name ASC';
    $orgres = mysql_query($orgqry);

    //pagination
    $adjacents = 5;
    $query = 'SELECT COUNT(*) FROM org WHERE avail = 0';
    $total_items = mysql_fetch_array(mysql_query($query));

    $targetpage = "manage.php";
    $limit = 20;
    if(isset($_GET['page'])) {
        $page = $_GET['page'];
        $start = ($page - 1) * $limit;
    } else {
        $page = 0;
        $start = 0;
    }

    //get data
    $getdata = 'SELECT * FROM org WHERE avail = 0 LIMIT '.$start.', '.$limit;
    $result = mysql_query($getdata);

    //setup page vars for display
    if ($page == 0) $page = 1;
    $prev = $page - 1;
    $next = $page + 1;
    $lastpage = ceil($total_items[0]/$limit);
    $lpm1 = $lastpage - 1;

    $pagination = "";
    if($lastpage > 1) {
        $pagination .= '<div class="pagination pagination-center"><ul>';

        //previous button
        if ($page > 1) {
            $pagination .= '<li><a href="'.$targetpage.'?page='.$prev.'">&laquo;</a></li>';
        } else {
            $pagination .= '<li class="disabled"><a href="#">&laquo;</a></li>';
        }

        //pages
        if ($lastpage < 7 + ($adjacents * 2)) /* not enough pages to bother breaking it up */ {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page) {
                    $pagination .= '<li class="active"><a href="#">'.$counter.'</a></li>';
                } else {
                    $pagination .= '<li><a href="'.$targetpage.'?page='.$counter.'">'.$counter.'</a></li>';
                }
            }
        } elseif($lastpage > 5 + ($adjacents * 2))  /* enough pages to hide some */ {
            //close to beginning; only hide later pages
            if($page < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a href="#">'.$counter.'</a></li>';
                    } else {
                        $pagination .= '<li><a href="'.$targetpage.'?page='.$counter.'">'.$counter.'</a></li>';
                    }
                }

                $pagination .= '<li><a href="#">...</a></li>';
                $pagination .= '<li><a href="'.$targetpage.'?page='.$lpm1.'">'.$lpm1.'</a></li>';
                $pagination .= '<li><a href="'.$targetpage.'?page='.$lastpage.'">'.$lastpage.'</a></li>';
            }

            //in middle; hide some front and some back
            elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                $pagination .= '<li><a href="'.$targetpage.'?page=1">1</a></li>';
                $pagination .= '<li><a href="'.$targetpage.'?page=2">2</a></li>';
                $pagination .= '<li><a href="#">...</a></li>';

                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a href="#">'.$counter.'</a></li>';
                    } else {
                        $pagination .= '<li><a href="'.$targetpage.'?page='.$counter.'">'.$counter.'</a></li>';
                    }
                }

                $pagination .= '<a href="#">...</a>';
                $pagination .= '<li><a href="'.$targetpage.'?page='.$lpm1.'">'.$lpm1.'</a></li>';
                $pagination .= '<li><a href="'.$targetpage.'?page='.$lastpage.'">'.$lastpage.'</a></li>';
            }

            //close to end; only hide early pages
            else {
                $pagination .= '<li><a href="'.$targetpage.'?page=1">1</a></li>';
                $pagination .= '<li><a href="'.$targetpage.'?page=2">2</a></li>';
                $pagination .= '<li><a href="#">...</a></li>';

                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a href="#">'.$counter.'</a></li>';
                    } else {
                        $pagination .= '<li><a href="'.$targetpage.'?page='.$counter.'">'.$counter.'</a></li>';
                    }
                }
            }
        } //pages

        //next button
        if ($page < $counter - 1) {
            $pagination .= '<li><a href="'.$targetpage.'?page='.$next.'">&raquo;</a></li>';
        } else {
            $pagination .= '<li class="disabled"><a href="#">&raquo;</a></li>';
        }

        $pagination .= '</ul></div>';
    } //$lastpage > 1

    //select proper info from members table
    switch($access){
        case 3: //manager
            $where = ' WHERE id = '.$usrid.' OR sub = '.$usrid;
            break;
        case 2: //administrator
            $where = ' WHERE id > 10 AND id <> 15 AND id <> 37 AND acc > 1';
            break;
        case 1: //root
            $where = '';
            break;
        default: //everyone else
            $where = ' WHERE id = '.$usrid;
    }
    $usrqry = 'SELECT * FROM members'.$where.' ORDER BY fname ASC';
    $usrres = mysql_query($usrqry);

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
                                    <li class="active">Manage</li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Manage    </h2>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                    </div> <!-- /container -->
                </section> <!-- /page-top -->

                <div class="container">
                    <div class="row">
                        <div class="span12">

                            <div class="tabs">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#organization" data-toggle="tab"><i class="icon-building"></i> Assigned Organizations (<?=mysql_num_rows($orgres);?>)</a></li>
                                    <li><a href="#mine" data-toggle="tab"><i class="icon-user"></i> Assigned to Me (<?=mysql_num_rows($mineres);?>)</a></li>
                                    <li><a href="#account" data-toggle="tab"><i class="icon-user"></i> Accounts (<?=mysql_num_rows($usrres);?>)</a></li>
                                    <li><a href="#messages" data-toggle="tab"><i class="icon-comment"></i> Notifications (<?=mysql_num_rows($msgres);?>)</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="organization">
                                    <?php
                                        //only show if user access level is 3 - manager, 2 - admin, or 1 - root
                                        if($access < 4){
                                    ?>
                                        <span class="pull-right"><a rel="tooltip" data-placement="top" href="#addorg" data-original-title="Add Organization" data-toggle="modal"><i class="icon-plus"></i><span class="alternative-font">&nbsp;Add Organization</span></a></span>
                                            <div id="addorg" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addorgLabel" aria-hidden="true">

                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h3 id="addorgLabel">Add New Organization</h3>
                                            </div> <!-- /modal-header -->

                                            <div class="modal-body">
                                                <form id="edit" action="" method="post">
                                                    <label class="span3">Organization:</label>
                                                    <input type="text" name="name" maxlength="100" class="span3">
                                                    <label class="span3">Category:</label>
                                                        <select name="catid">
                                                            <option value ="0" selected>Select Category:</option>
                                                            <option value = "0"></option>
                                                            <?php
                                                                $sql = 'SELECT * FROM cat ORDER BY title ASC';
                                                                $res = mysql_query($sql);
                                                                while($cat = mysql_fetch_array($res)) {
                                                                    echo '<option value="'.$cat['id'].'">'.$cat['title'].'</option>';
                                                                }
                                                            ?>
                                                        </select>
                                                    <label class="span3">Website:</label>
                                                    <input type="text" name="url" maxlength="100" class="span3">
                                                    <label class="span3">Point of Contact:</label>
                                                    <input type="text" name="poc_name" maxlength="100" class="span3">
                                                    <label class="span3">Contact Title/Position:</label>
                                                    <input type="text" name="poc_title" maxlength="100" class="span3">
                                                    <label class="span3">Phone Number (numbers only):</label>
                                                    <input type="text" name="poc_phone" maxlength="10" class="span3">
                                                    <label class="span3">Email:</label>
                                                    <input type="text" name="poc_email" maxlength="100" class="span3">
                                                    <label>Description:</label>
                                                    <textarea rows="3" class="span7" name="desc" form="edit"></textarea>
                                                </div> <!-- /modal-body -->

                                            <div class="modal-footer">
                                                    <input type="submit" name="submit" value="Add" class="btn btn-primary">
                                                </form>
                                            </div> <!-- /modal-footer -->
                                        </div> <!-- /addorg modal -->
                                        <?php
                                            }
                                        echo $pagination;
                                        ?>

                                        <table class="table table-striped">
                                            <thead>
                                                <th>Organization</th>
                                                <th>POC</th>
                                                <th>Phone #</th>
                                                <th>Email</th>
                                                <th>Notes</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    while($row = mysql_fetch_array($result)) {
                                                        $ntqry = 'SELECT * FROM notes WHERE org_id = '.$row['id'].' ORDER BY dt DESC';
                                                        $ntres = mysql_query($ntqry);
                                                        $note = mysql_fetch_array($ntres);
                                                        $notes = mysql_num_rows($ntres);
                                                            if($notes==0) {
                                                                $badge = '';
                                                            } else {
                                                                $badge = 'badge-success';
                                                            }
                                                        switch($row['donate']){
                                                            case 1:
                                                                $icon = 'thumbs-up';
                                                                $tooltip = 'Will donate :)';
                                                                break;
                                                            case 2:
                                                                $icon = 'thumbs-down';
                                                                $tooltip = 'Will not donate :(';
                                                                break;
                                                            default:
                                                                $icon = 'question';
                                                                $tooltip = 'Unknown if they will donate';
                                                        }
                                                        $orgname = (strlen($row['name']) > 23) ? substr($row['name'],0,20).'...' : $row['name'];
                                                        echo '<tr>';
                                                        echo '    <td><a rel="tooltip" data-placement="top" href="#" data-original-title="'.$tooltip.'"><i class="icon-'.$icon.'"></i></a>&nbsp;<a href="orginfo.php?orgid='.$row['id'].'" title="'.$row['name'].'">'.$orgname.'</a></td>';
                                                        echo '    <td>'.$row['poc_name'].'</td>';
                                                        echo '    <td>'.$row['poc_phone'].'</td>';
                                                        echo '    <td>'.$row['poc_email'].'</td>';
                                                        echo '    <td><a rel="tooltip" data-placement="top" href="notes.php?orgid='.$row['id'].'" data-original-title="'.$note['note'].'"><span class="badge '.$badge.'">'.$notes.'</span></a></td>';
                                                        echo '</tr>';
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                        <?php
                                            echo $pagination;
                                        ?>
                                    </div> <!-- /tab-pane organization -->

                                    <div class="tab-pane" id="mine">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Organization</th>
                                                <th>POC</th>
                                                <th>Phone #</th>
                                                <th>Email</th>
                                                <th>Notes</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    while($row = mysql_fetch_array($mineres)) {
                                                        $ntqry = 'SELECT * FROM notes WHERE org_id = '.$row['id'].' ORDER BY dt DESC';
                                                        $ntres = mysql_query($ntqry);
                                                        $note = mysql_fetch_array($ntres);
                                                        $notes = mysql_num_rows($ntres);
                                                            if($notes==0) {
                                                                $badge = '';
                                                            } else {
                                                                $badge = 'badge-success';
                                                            }
                                                        switch($row['donate']){
                                                            case 1:
                                                                $icon = 'thumbs-up';
                                                                break;
                                                            case 2:
                                                                $icon = 'thumbs-down';
                                                                break;
                                                            default:
                                                                $icon = 'question';
                                                        }
                                                        $orgname = (strlen($row['name']) > 23) ? substr($row['name'],0,20).'...' : $row['name'];
                                                        echo '<tr>';
                                                        echo '    <td><i class="icon-'.$icon.'"></i>&nbsp;<a href="orginfo.php?orgid='.$row['id'].'" title="'.$row['name'].'">'.$orgname.'</a></td>';
                                                        echo '    <td>'.$row['poc_name'].'</td>';
                                                        echo '    <td>'.$row['poc_phone'].'</td>';
                                                        echo '    <td>'.$row['poc_email'].'</td>';
                                                        echo '    <td><a rel="tooltip" data-placement="top" href="notes.php?orgid='.$row['id'].'" data-original-title="'.$note['note'].'"><span class="badge '.$badge.'">'.$notes.'</span></a></td>';
                                                        echo '</tr>';
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane mine -->

                                    <div class="tab-pane" id="account">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Username</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Assignments</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    $a = 0;
                                                    while($row = mysql_fetch_array($usrres)) {
                                                        $a++;
                                                        $asnqry = 'SELECT org.id AS orgid, org.name, org.usr_id FROM org JOIN members ON members.id = org.usr_id WHERE org.usr_id = '.$row['id'];
                                                        $asnres = mysql_query($asnqry);
                                                        $numasn = mysql_num_rows($asnres);

                                                        echo '<tr>';
                                                        echo '    <td><a href="#edituser'.$a.'" data-toggle="modal">'.$row['usr'].'</a></td>';
                                                        echo '    <td>'.$row['fname'].'</td>';
                                                        echo '    <td>'.$row['lname'].'</td>';
                                                        echo '    <td>'.$row['email'].'</td>';
                                                        echo '    <td><a href="#usrorgs'.$a.'" data-toggle="modal">'.$numasn.'</a></td>';
                                                        echo '</tr>';
                                                ?>
                                                    <!-- edituser modal -->
                                                        <div id="edituser<?=$a;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="edituser<?=$a;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="edituser<?=$a;?>Label">Edit Details for: <?=$row['usr'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <form action="" method="post">
                                                                    <label class="span3">First Name:</label>
                                                                    <input type="text" name="fname" value="<?=$row['fname'];?>" maxlength="30" class="span3">
                                                                    <label class="span3">Last Name:</label>
                                                                    <input type="text" name="lname" value="<?=$row['lname'];?>" maxlength="30" class="span3">
                                                                    <label class="span3">Email:</label>
                                                                    <input type="text" name="email" value="<?=$row['email'];?>" maxlength="30" class="span3">
                                                                    <label class="span3">Current Password:</label>
                                                                    <input type="password" name="password" value="" maxlength="30" class="span3">
                                                                    <label class="span3">New Password:</label>
                                                                    <input type="password" name="newpw" value="" maxlength="30" class="span3">
                                                                    <label class="span3">Verify New Password:</label>
                                                                    <input type="password" name="verpw" value="" maxlength="30" class="span3">
                                                                    <?php if($access == 1): ?>
                                                                    <label class="span3">Access Level:</label>
                                                                    <select name="acc">
                                                                        <?php
                                                                            $b = 1;
                                                                            while($b <= 4){
                                                                                $sel = '';
                                                                                if($row['acc'] == $b) {
                                                                                    $sel = 'selected';
                                                                                }
                                                                                switch($b){
                                                                                    case 1:
                                                                                        $acc = 'root';
                                                                                        break;
                                                                                    case 2:
                                                                                        $acc = 'Administrator';
                                                                                        break;
                                                                                    case 3:
                                                                                        $acc = 'Manager';
                                                                                        break;
                                                                                    case 4:
                                                                                        $acc = 'User';
                                                                                        break;
                                                                                    default:
                                                                                        $acc = 'Invalid';
                                                                                }
                                                                                echo '<option value="'.$b.'" '.$sel.'>'.$acc.'</option>';
                                                                                $b++;
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                    <?php
                                                                        endif;
                                                                    ?>
                                                                    <label class="span3">Classroom 1:</label>
                                                                    <select name="classid" class="span3">
                                                                        <option value="">Select Classroom:</option>
                                                                        <option value=""></option>
                                                                        <?php
                                                                            $sql = 'SELECT * FROM class';
                                                                            $res = mysql_query($sql);
                                                                            while($sub = mysql_fetch_array($res)) {
                                                                                $c1 = '';
                                                                                if($row['class_id'] == $sub['id']) {
                                                                                    $c1 = 'selected';
                                                                                }
                                                                                echo '<option value="'.$sub['id'].'" '.$c1.'>'.$sub['name'].'</option>';
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                    <label class="span3">Classroom 2:</label>
                                                                    <select name="classid2" class="span3">
                                                                        <option value="">Select Classroom:</option>
                                                                        <option value=""></option>
                                                                        <?php
                                                                            $sql2 = 'SELECT * FROM class';
                                                                            $res2 = mysql_query($sql2);
                                                                            while($sub2 = mysql_fetch_array($res2)) {
                                                                                $c2 = '';
                                                                                if($row['class_id2'] == $sub2['id']) {
                                                                                    $c2 = 'selected';
                                                                                }
                                                                                echo '<option value="'.$sub2['id'].'" '.$c2.'>'.$sub2['name'].'</option>';
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                    <?php
                                                                        if($access <> 4):
                                                                    ?>
                                                                    <label class="span3">Assignable?</label>
                                                                        <select name="assign"  class="span3">
                                                                            <option value="0"<? if($row['assign']==0){echo ' selected';}?>>No</option>
                                                                            <option value="1"<? if($row['assign']==1){echo ' selected';}?>>Yes</option>
                                                                        </select>
                                                                    <?php
                                                                        endif;
                                                                    ?>
                                                                    <label class="span3">Receive Emails from KAM?</label>
                                                                        <select name="opt_out"  class="span3">
                                                                            <option value="0"<? if($row['opt_out']==0){echo ' selected';}?>>Yes</option>
                                                                            <option value="1"<? if($row['opt_out']==1){echo ' selected';}?>>No</option>
                                                                        </select>
                                                            </div> <!-- /modal-body -->

                                                            <div class="modal-footer">
                                                                    <input type="hidden" name="usrid" value="<?=$row['id'];?>">
                                                                    <input type="submit" name="submit" value="Update" class="btn btn-primary">
                                                                    <?php
                                                                        if($access == 1){
                                                                            echo '<input type="submit" name="submit" value="Delete" class="btn btn-danger">';
                                                                        }
                                                                    ?>
                                                                </form>
                                                            </div> <!-- /modal-footer -->
                                                        </div> <!-- /edituser -->

                                                    <!-- usrorgs modal -->
                                                        <div id="usrorgs<?=$a;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="usrorgs<?=$a;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="usrorgs<?=$a;?>Label">Assigned Organizations for: <?=$row['usr'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <p>
                                                                <?php
                                                                    while($orgs = mysql_fetch_array($asnres)) {
                                                                        echo '<a href="orginfo.php?orgid='.$orgs['orgid'].'">'.$orgs['name'].'</a><br />';
                                                                    }
                                                                ?>
                                                                </p>
                                                            </div> <!-- /modal-body -->

                                                            <div class="modal-footer">

                                                            </div> <!-- /modal-footer -->
                                                        </div> <!-- /orgsincat -->
                                                <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane account -->

                                    <div class="tab-pane" id="messages">
                                        <span class="pull-right"><a rel="tooltip" data-placement="top" href="#addmsg" data-original-title="Send Note" data-toggle="modal"><i class="icon-comment"></i><span class="alternative-font">&nbsp;Send Message</span></a></span>
                                            <div id="addmsg" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addmsgLabel" aria-hidden="true">

                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h3 id="addmsgLabel">Send a Message</h3>
                                            </div> <!-- /modal-header -->

                                            <div class="modal-body">
                                                <form action="" method="post">
                                                    <label class="span1">To:</label>
                                                    <select name="usr_id" class="span3">
                                                        <option value="">Select user:</option>
                                                        <option value=""></option>
                                                        <?php
                                                            $sql = 'SELECT * FROM members WHERE usr NOT LIKE "root" AND sub <> 11 AND id <> '.$usrid.' ORDER BY fname ASC';
                                                            $res = mysql_query($sql);
                                                            while($row = mysql_fetch_array($res)) {
                                                                echo '<option value="'.$row['id'].'">'.mb_substr($row['fname'], 0, 1, 'utf-8').'.&nbsp;'.$row['lname'].'</option>';
                                                            }
                                                        ?>
                                                    </select>
                                                    <label>Message:</label>
                                                    <textarea rows="3" class="span7" name="text"></textarea>
                                                </div> <!-- /modal-body -->

                                            <div class="modal-footer">
                                                    <input type="submit" name="submit" value="Send" class="btn btn-primary">
                                                </form>
                                            </div> <!-- /modal-footer -->
                                        </div> <!-- /addmsg modal -->
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Message</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    $d = 0;
                                                    while($row = mysql_fetch_array($msgres)) {
                                                     $d++;
                                                        echo '<tr>';
                                                        if($row['new'] == 0) {
                                                            echo '    <td><strong>'.$row['text'].'</strong></td>';
                                                        } else {
                                                            echo '    <td>'.$row['text'].'</td>';
                                                        }
                                                        echo '    <td>'.$row['dt'].'</td>';
                                                        if($row['new'] == 0) {
                                                            echo '    <td><i class="icon-thumbs-up icon-2x" onclick="document.markread'.$d.'.submit();"></i>&nbsp;<i class="icon-remove icon-2x" onclick="document.delmsg'.$d.'.submit();"></i></td>';
                                                        } else {
                                                            echo '    <td><i class="icon-remove icon-2x" onclick="document.delmsg'.$d.'.submit();"></i></td>';
                                                        }
                                                        echo '</tr>';

                                                        echo '<form name="markread'.$d.'" action="" method="post"><input type="hidden" name="mark" value="'.$row['id'].'"></form>';
                                                        echo '<form name="delmsg'.$d.'" action="" method="post"><input type="hidden" name="del" value="'.$row['id'].'"></form>';
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane messages -->
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