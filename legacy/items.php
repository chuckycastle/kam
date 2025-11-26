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

    //if submit add
    if($_POST['submit']=='Add') {
        $itemdesc = val($_POST['itemdesc']);
        $value = val(preg_replace("/[^0-9]/","", $_POST['value']));
        if(val($_POST['itemnum'] == '')){
            $itemnum = 0;
        } else {
        $itemnum = val($_POST['itemnum']);
        }
        $received = val($_POST['rcv']);
        $msgsnt = 0;
        if($received==1){
            $emailqry = 'SELECT members.fname, members.email, org.name FROM members JOIN org ON org.usr_id = members.id JOIN items ON items.org_id = org.id WHERE org.id = '.$orgid.' AND items.msg_sent = 0';
            $emailres = mysql_query($emailqry);
            while($email = mysql_fetch_array($emailres)){
                send_mail(
                    'donotreply@krigercenter.org',
                    $email['email'],
                    'A donation has been received!',
                    'Hey '.$email['fname'].','."\n\n".'A donation has been received for '.$email['name']."\n\n".'You can now login at http://auction.kriegercenter.org to check it out.'."\n\n".'--'."\n".'Please do not reply to this email.'
                );
                $msgsnt = 1;
            }
        }
        $loc = val($_POST['loc']);
        $class = val($_POST['class']);
        $dt = date("Y-m-d H:i:s");

        $sql = 'INSERT INTO items (id, org_id, items.desc, value, itemnum, received, location, usr_id, class_id, dt, msg_sent) VALUES (NULL, "'.$orgid.'", "'.sentence_case($itemdesc).'", "'.$value.'", "'.$itemnum.'", "'.$received.'", "'.$loc.'", "'.$usrid.'", "'.$class.'", "'.$dt.'", "'.$msgsnt.'")';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Sweet! Item added!';
    }

    //if submit update
    if($_POST['submit']=='Update') {
        $itemdesc = val($_POST['itemdesc']);
        $value = val(preg_replace("/[^0-9]/","", $_POST['value']));
        if(val($_POST['itemnum'] == '')){
            $itemnum = 0;
        } else {
        $itemnum = val($_POST['itemnum']);
        }
        $received = val($_POST['rcv']);
        if($received==1){
            $emailqry = 'SELECT members.fname, members.email, org.name FROM members JOIN org ON org.usr_id = members.id JOIN items ON items.org_id = org.id WHERE items.id = "'.$_POST['itemid'].'" AND items.msg_sent = 0 LIMIT 1';
            $emailres = mysql_query($emailqry);
            while($email = mysql_fetch_array($emailres)){
                send_mail(
                    'donotreply@krigercenter.org',
                    $email['email'],
                    'A donation has been received!',
                    'Hey '.$email['fname'].','."\n\n".'A donation has been received for '.$email['name']."\n\n".'You can now login at http://auction.kriegercenter.org to check it out.'."\n\n".'--'."\n".'Please do not reply to this email.'
                );
                mysql_query('UPDATE items SET msg_sent = "1" WHERE items.id = '.$_POST['itemid']);
            }
        }
        $loc = val($_POST['loc']);
        $dt = date("Y-m-d H:i:s");
        $itemid = val($_POST['itemid']);
        $class = val($_POST['class']);

        $sql = 'UPDATE items SET items.desc="'.sentence_case($itemdesc).'", value='.$value.', itemnum='.$itemnum.', received='.$received.', location="'.$loc.'", usr_id='.$usrid.', class_id="'.$class.'", dt="'.$dt.'" WHERE items.id='.$itemid.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Right on!  You successfully updated the item!';
    }

    //if submit delete
    if($_POST['submit']=='Delete') {
        $itemid = val($_POST['itemid']);

        $sql = 'DELETE FROM items WHERE items.id = '.$itemid.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['info']='Right on!  You successfully deleted the item!';
    }

//SQL

    //select info from org table
    $orgqry = 'SELECT * FROM org WHERE id = '.$orgid;
    $orgres = mysql_query($orgqry);
    $orgrow = mysql_fetch_array($orgres);

    //select info from items table
    $itmqry = 'SELECT items.id, items.org_id, items.desc, items.value, items.itemnum, items.received, items.location, items.dt, members.usr, members.fname, members.lname FROM items INNER JOIN members ON items.usr_id=members.id WHERE org_id = '.$orgid.' ORDER BY items.dt DESC';
    $itmres = mysql_query($itmqry);
    $items = mysql_num_rows($itmres);

    //select info from class table
    $clsqry = 'SELECT class.id, class.title FROM class JOIN members on members.class_id = class.id';
    $clsres = mysql_query($clsqry);

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
                                    <li><a href="orginfo.php?orgid=<?=$orgid;?>"><?=$orgrow['name'];?></a><span class="divider">/</span></li>
                                    <li class="active">Items</a></li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Items</h2>
                            </div>
                        </div>
                    </div> <!-- /container -->
                </section>

                <div class="container">

                    <div class="row">
                        <div class="span6">
                            <h3><?=$orgrow['name'].' Items ('.$items.')';?></h3>
                        </div>
                        <div class="span6">
                            <p class="pull-right">
                                <a href="#additem" data-toggle="modal"><i class="icon-pencil icon-large"></i><span class="alternative-font">&nbsp;Add Item</span></a>
                            </p>
                        </div> <!-- /span6 -->
                    </div> <!-- /row -->

                    <div id="items" class="row">
                        <div class="span12">
                            <ul class="comments">
                                <?php
                                    $i = 0;
                                    while($row = mysql_fetch_array($itmres)) {
                                    $i++;
                                ?>
                                <li>
                                    <div class="comment">
                                        <div class="comment-block">
                                            <?php if($access < 3): ?>
                                            <span class="pull-right">
                                                <a rel="tooltip" data-placement="top" href="#edititem<?=$i;?>" data-original-title="Edit" data-toggle="modal"><i class="icon-edit icon-2x"></i></a>
                                            </span>
                                            <!-- edititem modal -->
                                            <div id="edititem<?=$i;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="edititemLabel" aria-hidden="true">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    <h3 id="edititemLabel">Edit Item</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" method="post">
                                                        <label class="span3">Item Received?</label>
                                                        <select name="rcv"  class="span3">
                                                            <option value="0"<?php if($row['received']==0){echo ' selected';} ?>>No</option>
                                                            <option value="1"<?php if($row['received']==1){echo ' selected';} ?>>Yes</option>
                                                        </select>
                                                        <label class="span3">Item Number:</label>
                                                        <input type="text" name="itemnum" value="<?=$row['itemnum'];?>" class="span3">
                                                        <label class="span3">Value (Whole dollars only):</label>
                                                        <input type="text" name="value" value="<?=$row['value'];?>" class="span3">
                                                        <label class="span3">Location:</label>
                                                        <input type="text" name="loc" value="<?=$row['location'];?>" class="span3">
                                                        <label>Description:</label>
                                                        <textarea name="itemdesc" maxlength="1200" rows="5" class="span6"><?=$row['desc'];?></textarea>
                                                        <label class="span3">
                                                            Credit goes to:
                                                        </label>
                                                        <select name="class">
                                                        <?php
                                                            $sql2 = 'SELECT class.id AS classid, class.name FROM class JOIN members ON members.class_id = class.id OR members.class_id2 = class.id JOIN org ON org.usr_id = members.id JOIN items ON items.org_id = org.id WHERE org.id = '.$orgid;
                                                            $res2 = mysql_query($sql2);
                                                            while($row2 = mysql_fetch_array($res2)) {
                                                                echo '<option value="'.$row2['classid'].'">'.$row2['name'].'</option>';
                                                            }
                                                        ?>
                                                        </select>

                                                </div>
                                                <div class="modal-footer">
                                                        <input type="hidden" name="itemid" value="<?=$row['id'];?>">
                                                        <input type="submit" name="submit" value="Update" class="btn btn-primary">
                                                        <input type="submit" name="submit" value="Delete" class="btn btn-danger">
                                                    </form>
                                                </div>
                                            </div> <!-- /edititem -->
                                            <?php endif; ?>
                                            <span class="comment-by">
                                                <i class="icon-user"></i>
                                                <strong><?php echo mb_substr($row['fname'], 0, 1, 'utf-8').'.&nbsp;'.$row['lname']; ?></strong>
                                            </span>
                                            <p><?=$row['desc'];?></p>
                                            <span class="date pull-right">
                                                <strong>Item #: </strong><?=$row['itemnum'];?>&nbsp;|&nbsp;
                                                <strong>Value: </strong>$<?=$row['value'];?>&nbsp;|&nbsp;
                                                <strong>Received: </strong><? if($row['received']==1){ echo 'Yes'; } else { echo 'No';};?>&nbsp;|&nbsp;
                                                <strong>Location: </strong><?=$row['location'];?>&nbsp;|&nbsp;
                                                <?=$row['dt'];?>
                                            </span>
                                        </div>
                                    </div> <!-- /comment -->
                                </li>
                                <?php
                                    }
                                ?>
                            </ul>
                        </div> <!-- /span12 -->
                    </div> <!-- /row -->
                </div> <!-- /container -->
            </div> <!-- /main -->

<!-- additem modal -->
<div id="additem" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="additemLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="additemLabel">Add item to <?=$orgrow['name'];?></h3>
    </div>
    <div class="modal-body">
        <form action="" method="post">
            <label class="span3">Item Received?</label>
            <select name="rcv"  class="span3">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
            <label class="span3">Item Number:</label>
            <input type="text" name="itemnum" class="span3">
            <label class="span3">Value (Whole dollars only):</label>
            <input type="text" name="value" class="span3">
            <label class="span3">Location:</label>
            <input type="text" name="loc" class="span3">
            <label>Description:</label>
        <textarea name="itemdesc" maxlength="1200" rows="5" class="span6"></textarea>
        <label class="span3">
            Credit goes to:
        </label>
        <select name="class">
            <?php
                $sql = 'SELECT class.name FROM class JOIN members ON members.class_id = class.id OR members.class_id2 = class.id JOIN org ON org.usr_id = members.id JOIN items ON items.org_id = org.id WHERE org.id = '.$orgid;
                $res = mysql_query($sql);
                while($row = mysql_fetch_array($res)) {
                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                }
            ?>
        </select>
    </div>
    <div class="modal-footer">
            <input type="submit" name="submit" value="Add" class="btn btn-primary">
        </form>
    </div>
</div> <!-- /additem -->


<?php
//include html footer
    include('inc/footer.php');
?>
