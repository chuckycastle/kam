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

    //if navigate to page directly, refresh to index.php
    if(!isset($_GET['orgid'])) {
        $_SESSION['msg']['warning']='My bad, can\'t let you do that :/';

        header("Location: index.php");
        exit;
    }

//set variables
    $orgid = $_GET['orgid'];
    $usrid = $_SESSION['id'];
    $usr = $_SESSION['usr'];
    $access = $_SESSION['acc'];

//POST

    //if submit share
    if($_POST['submit']=='Share') {
        $name = val($_POST['name']);
        $poc_name = nameize(val($_POST['poc_name']));
        $poc_title = val($_POST['poc_title']);
        $poc_phone = val($_POST['poc_phone']);
        $poc_email = val($_POST['poc_email']);
        $desc = sentence_case(val($_POST['desc']));

        send_mail(    'donotreply@kriegercenter.org',
        $_POST['email'],
        'Krieger Auction info for '.$name,
        'Hey there!'."\n\n".$_SESSION['usr'].' over at the Krieger Auction site wanted you to have this info on '.$name.'.'."\n\n".$_POST['comment']."\n\n".'Point of contact: '.$poc_name.', '.$poc_title."\n".'Phone number: '.$poc_phone."\n".'Email: '.$poc_email."\n\n".'And, a little something about them: '."\n".$desc."\n\n\n".'--'."\n".'Please do not reply to this email.');

        $_SESSION['msg']['success']='Right on!  You successfully shared information on '.$name.' with '.$_POST['email'].'!';
    }

    //if submit assign
    if($_POST['submit']=='Assign') {
        $name = nameize(val($_POST['name']));
        $poc_name = nameize(val($_POST['poc_name']));
        $poc_title = val($_POST['poc_title']);
        $poc_phone = val($_POST['poc_phone']);
        $poc_email = val($_POST['poc_email']);
        $desc = sentence_case(val($_POST['desc']));

        $sql = 'UPDATE org SET avail = 0, usr_id = '.$_POST['userid'].' WHERE id = '.$orgid;
        $res = mysql_query($sql);

        if($_POST['userid'] != $usrid){
            $query = 'SELECT * FROM members WHERE members.id = '.$_POST['userid'].' LIMIT 1';
            $query = mysql_query($query);
            $data = mysql_fetch_array($query);
            send_mail(    'donotreply@kriegercenter.org',
            $data['email'],
            'Krieger Auction info for '.$name,
            'Hey '.$data['fname'].','."\n\n".$_SESSION['usr'].' over at the Krieger Auction Manager just assigned '.$name.' to you!  This is the POC info we have so far:'."\n\n".'Point of contact: '.$poc_name.', '.$poc_title."\n".'Phone number: '.$poc_phone."\n".'Email: '.$poc_email."\n\n".'And, a little something about them: '."\n".$desc."\n\n".'You can log in at http://auction.kriegercenter.org and check out the "My Assignments" tab under the "Manage" menu.'."\n\n\n".'--'."\n".'Please do not reply to this email.');
        }

        $_SESSION['msg']['success']='Woo hoo! You just updated the user assignment for '.$name.'!';
    }

    //if submit clear
    if($_POST['submit']=='Clear') {
        $name = nameize(val($_POST['name']));

        $sql = 'UPDATE org SET avail = 1, usr_id = 0 WHERE id = '.$orgid;
        $res = mysql_query($sql);

        $_SESSION['msg']['info']='Awesome!  You cleared all user assignments from '.$name.'.';
    }

    //if submit update
    if($_POST['submit']=='Update') {
        $name = val($_POST['name']);
        $cat_id = val($_POST['catid']);
        $regex = '/(?<!href=["\'])http:\/\//';
        $url = preg_replace($regex, '', $_POST['url']);
        $desc = sentence_case(val($_POST['desc']));
        $poc_name = nameize(val($_POST['poc_name']));
        $poc_title = val($_POST['poc_title']);
        $poc_email = val($_POST['poc_email']);
        $poc_phone = formatphone(val(preg_replace("/[^0-9]/","", $_POST['poc_phone'])));
        $don = val($_POST['don']);
        $id = val($_POST['id']);

        $sql = 'UPDATE org SET name="'.$name.'", cat_id='.$cat_id.', url="'.$url.'", org.desc="'.$desc.'", poc_name="'.$poc_name.'", poc_title="'.$poc_title.'", poc_email="'.$poc_email.'", poc_phone="'.$poc_phone.'", donate="'.$don.'" WHERE org.id='.$orgid.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Right on!  You successfully updated information for '.$name.'!';
    }

    //if submit delete
    if($_POST['submit']=='Delete') {
        $name = nameize(val($_POST['name']));
        //delete organization
        mysql_query('DELETE FROM org WHERE id = '.$orgid.' LIMIT 1');
        //delete notes/items
        mysql_query('DELETE FROM notes WHERE org_id = '.$orgid);
        mysql_query('DELETE FROM items WHERE org_id = '.$orgid);

        $_SESSION['msg']['info']='Right on!  You successfully deleted '.$name.'!';
        header('Location: manage.php?tab=org');
    }

//SQL

    //select proper info from org table
    $assign = 'SELECT * FROM org WHERE id = '.$orgid.' AND org.usr_id <> 0';
    if(mysql_num_rows(mysql_query($assign))==0) {
        $orgqry = 'SELECT * FROM org WHERE id = '.$orgid;
    } else {
        $orgqry = 'SELECT * FROM org INNER JOIN members ON org.usr_id = members.id WHERE org.id = '.$orgid;
    }
    $orgres = mysql_query($orgqry);
    $org = mysql_fetch_array($orgres);

    //select info from members table
    $usrqry = 'SELECT * FROM members WHERE assign = 1 ORDER BY usr ASC';
    $usrres = mysql_query($usrqry);

//include HTML header
    include('inc/header.php');
?>

            <div role="main" class="main">

                <section class="page-top">
                    <div class="container">
                        <div class="row">
                            <div class="span12">
                                <ul class="breadcrumb">
                                    <li><a href="index.php">Home</a><span class="divider">/</span></li>
                                    <li class="active"><?php echo $org['name'];?></li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Information</h2>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                    </div> <!-- /container -->
                </section> <!-- /page-top -->

                <div class="container">

                    <div id="contact-info" class="row">
                        <div class="span6">
                            <p class="lead">
                            <?php
                                $where = '';
                                if($org['avail']==0){
                                    echo '<a rel="tooltip" data-placement="top" data-original-title="'.mb_substr($org['fname'], 0, 1, 'utf-8').'.&nbsp;'.$org['lname'].'"><i class="icon-user"></i></a>';
                                }
                            ?>
                                <span class="alternative-font"><?php echo $org['name']; ?></span>
                                <?php
                                    switch($org['donate']){
                                        case 1:
                                            $icon = 'thumbs-up';
                                            $tooltip = 'Will donate :)';
                                            break;
                                        case 2:
                                            $icon = 'thumbs-down';
                                            $tooltip = 'Will not donate';
                                            break;
                                        default:
                                            $icon = 'question';
                                            $tooltip = 'Unknown if they will donate';
                                    }

                                    echo '<a rel="tooltip" data-placement="top" data-original-title="'.$tooltip.'"><i class="icon-'.$icon.'"></i></a>';
                                ?>
                                <br />
                                <?php
                                    if($org['cat_id']=="0"){
                                        echo '';
                                    } else {
                                        $catqry = 'SELECT cat.id, cat.title FROM cat WHERE cat.id = "'.$org['cat_id'].'" LIMIT 1';
                                        $catres = mysql_query($catqry);
                                        while($row = mysql_fetch_array($catres)){
                                            echo $row['title'];
                                        }
                                    }
                                ?>
                                <br />
                                <?php echo '<a href="http://'.$org['url'].'" target="_blank">'.$org['url']?></a>
                            </p>
                        </div> <!-- /span6 -->
                        <div class="span3">
                            <h5>Point of Contact</h5>
                            <p>
                                <?php
                                    echo '<strong>'.$org['poc_name'].'</strong>&nbsp;'.$org['poc_title'].'<br />';
                                    echo '<i class="icon-phone">&nbsp;</i>'.$org['poc_phone'].'<br />';
                                    echo '<i class="icon-envelope">&nbsp;</i>'.$org['poc_email'].'<br />';
                                ?>
                            </p>
                        </div> <!-- /span3 -->
                        <div id="icons" class="span3 pull-right">
                            <p id="icons">
                            <?php
                                $where = '';
                                if($access == 4){
                                    if($org['usr_id']==$usrid || $org['usr_id']==0) {
                                        echo '<a rel="tooltip" data-placement="top" href="#assign" data-original-title="Update user assignment" data-toggle="modal"><i class="icon-group icon-2x"></i>&nbsp;</a>';
                                        $where = ' WHERE members.id = '.$usrid;
                                    }
                                }

                                //show edit if admin or
                                if($usrid = $org['usr_id'] || $access <= 3){
                                    echo '<a rel="tooltip" data-placement="top" href="#edit" data-original-title="Edit details" data-toggle="modal"><i class="icon-gear icon-2x"></i>&nbsp;</a>';
                                }

                                //only show if user access level is 3 - manager, 2 - admin, or 1 - root
                                if($access <= 3){
                                    echo '<a rel="tooltip" data-placement="top" href="#assign" data-original-title="Update user assignment" data-toggle="modal"><i class="icon-group icon-2x"></i>&nbsp;</a>';
                                    echo '<a rel="tooltip" data-placement="top" href="items.php?orgid='.$orgid.'" data-original-title="Items" data-toggle="modal"><i class="icon-tag icon-2x"></i>&nbsp;</a>';
                                }
                            ?>
                                <a rel="tooltip" data-placement="top" href="notes.php?orgid=<?php echo $orgid;?>" data-original-title="Notes"><i class="icon-edit icon-2x"></i>&nbsp;</a>
                                <a rel="tooltip" data-placement="top" href="#share" data-original-title="Share info" data-toggle="modal"><i class="icon-share icon-2x"></i>&nbsp;</a>

                            </p> <!-- /icons -->
                        </div> <!-- /icons span3 -->
                    </div> <!-- /contact-info -->

                    <div id="description" class="row">
                        <div class="span12">
                            <p><?php echo $org['desc']?></p>
                        </div> <!-- /span12 -->
                    </div> <!-- /description -->
                </div> <!-- /container -->

            </div> <!-- /main -->

            <!-- share modal -->
            <div id="share" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="shareLabel" aria-hidden="true">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="shareLabel">Share <?php echo $org['name'];?> Details</h3>
                </div> <!-- /modal-header -->

                <div class="modal-body">
                    <form action="" method="post">
                        <label>Recipient's Email Address:</label>
                        <input type="text" name="email" value maxlength="100" class="span3" />
                        <label>Additional Comments:</label>
                        <textarea name="comment" maxlength="5000" rows="10" class="span6"></textarea>
                </div> <!-- /modal-body -->

                <div class="modal-footer">
                        <input type="hidden" name="name" value="<?php echo $org['name'];?>">
                        <input type="hidden" name="poc_name" value="<?php echo $org['poc_name'];?>">
                        <input type="hidden" name="poc_title" value="<?php echo $org['poc_title'];?>">
                        <input type="hidden" name="poc_phone" value="<?php echo $org['poc_phone'];?>">
                        <input type="hidden" name="poc_email" value="<?php echo $org['poc_email'];?>">
                        <input type="hidden" name="desc" value="<?php echo $org['desc'];?>">
                        <input type="submit" name="submit" value="Share" class="btn btn-primary">
                    </form>
                </div> <!-- /modal-footer -->
            </div> <!-- /share modal -->

            <!-- assign modal -->
            <div id="assign" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="assignLabel" aria-hidden="true">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="assignLabel">Assign User to <?php echo $org['name'];?></h3>
                </div> <!-- /modal-header -->

                <div class="modal-body">
                    <form action="" method="post">
                        <label>Select User:</label>
                        <select name="userid" class="span3">
                            <option value="0">Select User:</option>
                            <option value="0"></option>
                            <?php
                                $sql = 'SELECT * FROM members'.$where.' ORDER BY fname ASC';
                                $res = mysql_query($sql);
                                while($sub = mysql_fetch_array($res)) {
                                    $u1 = '';
                                    if($org['usr_id'] == $sub['id']) {
                                        $u1 = 'selected';
                                    }
                                        echo '<option value="'.$sub['id'].'" '.$u1.'>'.mb_substr($sub['fname'], 0, 1, 'utf-8').'.&nbsp;'.$sub['lname'].'</option>';
                                    }
                                ?>
                        </select>
                </div> <!-- /modal-body -->

                <div class="modal-footer">
                        <input type="hidden" name="name" value="<?php echo $org['name'];?>">
                        <input type="hidden" name="poc_name" value="<?php echo $org['poc_name'];?>">
                        <input type="hidden" name="poc_title" value="<?php echo $org['poc_title'];?>">
                        <input type="hidden" name="poc_phone" value="<?php echo $org['poc_phone'];?>">
                        <input type="hidden" name="poc_email" value="<?php echo $org['poc_email'];?>">
                        <input type="hidden" name="desc" value="<?php echo $org['desc'];?>">
                        <input type="submit" name="submit" value="Assign" class="btn btn-primary">
                        <input type="submit" name="submit" value="Clear" class="btn btn-danger">
                    </form>
                </div> <!-- /modal-footer -->
            </div> <!-- /assign modal -->

<?php
    //only show if user access level is 3 - manager, 2 - admin, or 1 - root
    if($usrid = $org['usr_id'] || $access <= 3){
?>
            <!-- edit modal -->
            <div id="edit" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="editLabel">Edit <?php echo $org['name'];?> Details</h3>
                </div> <!-- /modal-header -->

                <div class="modal-body">
                    <form action="" method="post">
                        <label>Organization:</label>
                            <input type="text" name="name" value="<?php echo $org['name'];?>" maxlength="100" class="span6">
                            <label>Category:</label>
                            <select name="catid">
                                <option value="0">Select:</option>
                                <option value="0"></option>
                                <?php
                                    $sql = 'SELECT * FROM cat ORDER BY title ASC';
                                    $res = mysql_query($sql);
                                    while($cat = mysql_fetch_array($res)) {
                                        $sel = '';
                                        if($org['cat_id'] == $cat['id']){
                                            $sel = 'selected';
                                        }
                                        echo '<option value="'.$cat['id'].'" '.$sel.'>'.$cat['title'].'</option>';
                                    }
                                ?>
                            </select>
                            <label>Website:</label>
                            <input type="text" name="url" value="<?php echo $org['url'];?>" maxlength="100" class="span6">
                            <label>Will they donate this year?</label>
                            <select name="don">
                                <?php
                                    $i = 0;
                                    while($i <= 2){
                                        $sel = '';
                                        if($org['donate'] == $i) {
                                            $sel = 'selected';
                                        }
                                        switch($i){
                                            case 0:
                                                $sub = 'Unknown';
                                                break;
                                            case 1:
                                                $sub = 'Yes';
                                                break;
                                            case 2:
                                                $sub = 'No';
                                                break;
                                            default:
                                                $sub = 'Invalid';
                                        }
                                            echo '<option value="'.$i.'" '.$sel.'>'.$sub.'</option>';
                                        $i++;
                                    }
                                ?>
                            </select>
                            <label>Point of Contact:</label>
                            <input type="text" name="poc_name" value="<?php echo $org['poc_name'];?>" maxlength="100" class="span6">
                            <label>Contact Title/Position:</label>
                            <input type="text" name="poc_title" value="<?php echo $org['poc_title'];?>" maxlength="100" class="span6">
                            <label>Phone Number (numbers only):</label>
                            <input type="text" name="poc_phone" value="<?php echo $org['poc_phone'];?>" maxlength="10" class="span6">
                            <label>Email:</label>
                            <input type="text" name="poc_email" value="<?php echo $org['poc_email'];?>" maxlength="100" class="span6">
                            <label>Description:</label>
                            <textarea rows="4" class="span6" name="desc"><?php echo $org['desc'];?></textarea>
                </div> <!-- /modal-body -->

                <div class="modal-footer">
                        <input type="submit" name="submit" value="Update" class="btn btn-primary">
                        <?php if($access == 1): ?>
                        <input type="submit" name="submit" value="Delete" class="btn btn-danger">
                        <?php endif; ?>
                    </form>
                </div> <!-- /modal-footer -->
            </div> <!-- /edit modal -->

<?php
    }
    include('inc/footer.php');
?>