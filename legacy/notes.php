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
        $note = val($_POST['note']);
        $dt = date("Y-m-d H:i:s");

        $sql = 'INSERT INTO notes (id, org_id, usr_id, dt, note) VALUES (NULL, '.$orgid.', '.$usrid.', "'.$dt.'", "'.sentence_case($note).'")';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Sweet! Note added!';
    }
    
    //if submit update
    if($_POST['submit']=='Update') {
        $notes = val($_POST['notes']);
        $noteid = val($_POST['noteid']);
        
        $sql = 'UPDATE notes SET note="'.sentence_case($notes).'", usr_id='.$usrid.', dt = now() WHERE notes.id='.$noteid.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Right on!  You successfully updated the note!';
    }
    
    //if submit delete
    if($_POST['submit']=='Delete') {
        $noteid = val($_POST['noteid']);
        
        $sql = 'DELETE FROM notes WHERE notes.id = '.$noteid.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['info']='Right on!  You successfully deleted the note!';
    }

//SQL
    
    //select info from org table
    $orgqry = 'SELECT * FROM org WHERE id = '.$orgid;
    $orgres = mysql_query($orgqry);
    $orgrow = mysql_fetch_array($orgres);

    //select info from notes table
    $ntqry = 'SELECT notes.id, notes.dt, notes.note, notes.org_id, members.usr, members.fname, members.lname FROM notes INNER JOIN members ON notes.usr_id=members.id WHERE org_id = '.$orgid.' ORDER BY notes.dt DESC';
    $ntres = mysql_query($ntqry);
    $notes = mysql_num_rows($ntres);
    
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
                                    <li class="active">Notes</a></li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Notes</h2>
                            </div>
                        </div>
                    </div> <!-- /container -->
                </section>

                <div class="container">
                
                    <div class="row">
                        <div class="span6">
                            <h3><?=$orgrow['name'].' Notes ('.$notes.')';?></h3>
                        </div>
                        <div class="span6">
                            <p class="pull-right">
                                <a href="#addnote" data-toggle="modal"><i class="icon-pencil icon-2x"></i><span class="alternative-font">&nbsp;Add Note</span></a>
                            </p>    
                        </div> <!-- /span6 -->
                    </div> <!-- /row -->
                
                    <div id="notes" class="row">
                        <div class="span12">
                            <ul class="comments">
                                <?php
                                    $i = 0;
                                    while($row = mysql_fetch_array($ntres)) {
                                    $i++;
                                ?>
                                <li>
                                    <div class="comment">
                                        <div class="comment-block">
                                            <?php if($access < 3): ?>
                                            <span class="pull-right">
                                                <a rel="tooltip" data-placement="top" href="#editnote<?=$i;?>" data-original-title="Edit" data-toggle="modal"><i class="icon-edit"></i></a>
                                            </span>
                                            <!-- editnote modal -->
                                            <div id="editnote<?=$i;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="editnoteLabel" aria-hidden="true">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    <h3 id="editnoteLabel">Edit Note</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" method="post">
                                                        <label>Note:</label>
                                                        <textarea rows="6" class="span6" name="notes"><?=sentence_case($row['note'])?></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                        <input type="hidden" name="noteid" value="<?=$row['id'];?>">
                                                        <input type="submit" name="submit" value="Update" class="btn btn-primary">
                                                        <input type="submit" name="submit" value="Delete" class="btn btn-danger">
                                                    </form>
                                                </div>
                                            </div> <!-- /editnote -->    
                                            <?php endif; ?>
                                            <span class="comment-by">
                                                <i class="icon-user"></i>
                                                <strong><?php echo mb_substr($row['fname'], 0, 1, 'utf-8').'.&nbsp;'.$row['lname']; ?></strong>
                                            </span>
                                            <p><?=$row['note'];?></p>
                                            <span class="date pull-right"><?=$row['dt'];?></span>
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

<!-- addnote modal -->
<div id="addnote" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addnoteLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="addnoteLabel">Add note to <?=$orgrow['name'];?></h3>
    </div>
    <div class="modal-body">
        <form action="" method="post">
            <label>Note:</label>
        <textarea name="note" maxlength="5000" rows="10" class="span6"></textarea>
    </div>
    <div class="modal-footer">
            <input type="submit" name="submit" value="Add" class="btn btn-primary">
        </form>
    </div>
</div> <!-- /addnote -->


<?php
    include('inc/footer.php');
?>
