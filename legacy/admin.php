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

    //if access > 2
    if($_SESSION['acc'] > 2) {
        $_SESSION['msg']['error']='Sowwy... mommy and daddy don\'t want you to see that :(';

        header("Location: index.php");
        exit;
    }

//set variables
    $orgid = $_GET['orgid'];
    $usrid = $_SESSION['id'];
    $usr = $_SESSION['usr'];
    $access = $_SESSION['acc'];

//POST
    //if submit updclass
    if($_POST['submit']=='Update') {
        $id = val($_POST['classid']);

        $updqry = 'SELECT * FROM class WHERE id = '.$id.' LIMIT 1';
        $updres = mysql_query($updqry);
        $class = mysql_fetch_array($updres);

        if(!isset($_POST['name'])){
            $name = $class['name'];
        } else {
            $name = val($_POST['name']);
        }
        if(!isset($_POST['numkids'])){
            $class = $class['numkids'];
        } else {
            $numkids = val($_POST['numkids']);
        }
        if(!isset($_POST['parent1'])){
            $parent1 = $class['parent1'];
        } else {
            $parent1 = val($_POST['parent1']);
        }
        if(!isset($_POST['parent2'])){
            $parent2 = $class['parent2'];
        } else {
            $parent2 = val($_POST['parent2']);
        }

        $sql = 'UPDATE class SET name="'.nameize($name).'", numkids='.$numkids.', parent1="'.$parent1.'", parent2="'.$parent2.'" WHERE class.id='.$id.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Right on!  You successfully updated information for '.$class['name'].'!';
    }

    //if submit updcat
    if($_POST['submit']=='Update Category') {
        $id = val($_POST['catid']);

        $updqry = 'SELECT * FROM cat WHERE id = '.$id.' LIMIT 1';
        $updres = mysql_query($updqry);
        $cat = mysql_fetch_array($updres);

        if(!isset($_POST['title'])){
            $title = $class['title'];
        } else {
            $title = val($_POST['title']);
        }

        $sql = 'UPDATE cat SET title="'.$title.'" WHERE cat.id='.$id.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['success']='Right on!  You successfully updated information for '.$cat['title'].'!';
    }

    //if submit addcat
    if($_POST['submit']=='Add Category') {
        $title = val($_POST['title']);
        if($title==''){
            $_SESSION['msg']['error']='Oh noes!  You didn\'t enter a category title.';
        } else {
            $sql = 'INSERT INTO cat (id, title) VALUES (NULL, "'.$title.'")';
            $res = mysql_query($sql);
        }
    }

    //if submit delete
    if($_POST['submit']=='Delete') {
        $catid = val($_POST['catid']);

        $sql = 'DELETE FROM cat WHERE cat.id = '.$catid.' LIMIT 1';
        $res = mysql_query($sql);

        $_SESSION['msg']['info']='Right on!  You successfully deleted the category!';
    }

//SQL

    //classroom data
    $classqry = 'SELECT * FROM class WHERE campus NOT LIKE 0 GROUP BY name ORDER BY name ASC';
    $classres = mysql_query($classqry);

    //category data
    $catqry = 'SELECT * FROM cat ORDER BY title ASC';
    $catres = mysql_query($catqry);

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
                                    <li class="active">Admin</li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Admin    </h2>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                    </div> <!-- /container -->
                </section> <!-- /page-top -->

                <div class="container">
                    <div class="row">
                        <div class="span12">

                            <div class="tabs">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#classrooms" data-toggle="tab"><i class="icon-group"></i> Classrooms (<?=mysql_num_rows($classres);?>)</a></li>
                                    <li><a href="#categories" data-toggle="tab"><i class="icon-folder-open"></i> Categories (<?=mysql_num_rows($catres);?>)</a></li>
                                    <?php
                                        if($access==1){
                                            echo '<li><a href="#bfg" data-toggle="tab"><i class="icon-excel"></i> BFG (<?=mysql_num_rows($bfgres);?>)</a></li>';
                                        }
                                    ?>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="classrooms">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Classroom</th>
                                                <th># of Kids</th>
                                                <th>Parent 1</th>
                                                <th>Parent 2</th>
                                                <th>Registered Accounts</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    $a = 0;
                                                    while($row = mysql_fetch_array($classres)) {
                                                        $a++;
                                                        $sql = 'SELECT class.id, class.name, members.fname, members.lname FROM members, class WHERE class.parent1 = members.id AND class.id = '.$row['id'].' LIMIT 1';
                                                        $res = mysql_query($sql);
                                                        $parent1 = mysql_fetch_array($res);
                                                        $sql = 'SELECT class.id, class.name, members.fname, members.lname FROM members, class WHERE class.parent2 = members.id AND class.id = '.$row['id'].' LIMIT 1';
                                                        $res = mysql_query($sql);
                                                        $parent2 = mysql_fetch_array($res);

                                                        $regqry = 'SELECT usr FROM members WHERE fname <> "X" AND class_id = '.$row['id'].' OR class_id2 = '.$row['id'].' group by usr';
                                                        $regres = mysql_query($regqry);
                                                        $regnum = mysql_num_rows($regres);

                                                        echo '<tr>';
                                                        echo '    <td><a href="#editclass'.$a.'" data-toggle="modal">'.$row['name'].'</a></td>';
                                                        echo '    <td>'.$row['numkids'].'</td>';
                                                        echo '    <td>'.mb_substr($parent1['fname'], 0, 1, 'utf-8').'.&nbsp;'.$parent1['lname'].'</td>';
                                                        echo '    <td>'.mb_substr($parent2['fname'], 0, 1, 'utf-8').'.&nbsp;'.$parent2['lname'].'</td>';
                                                        echo '    <td>'.$regnum.'</td>';
                                                        echo '</tr>';
                                                ?>
                                                    <!-- editclass modal -->
                                                        <div id="editclass<?=$a;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="editclass<?=$a;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="editclass<?=$a;?>Label">Edit Details for: <?=$row['name'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <form action="" method="post">
                                                                    <label class="span3">Classroom:</label>
                                                                    <input type="text" name="name" value="<?=$row['name'];?>" maxlength="30" class="span3">
                                                                    <label class="span3">Number of Kids:</label>
                                                                    <input type="text" name="numkids" value="<?=$row['numkids'];?>" maxlength="30" class="span3">
                                                                    <label class="span3">Parent 1:</label>
                                                                    <select name="parent1" class="span3">
                                                                        <option value="0">Select user:</option>
                                                                        <?php
                                                                            $sql = 'SELECT id, fname, lname, class_id FROM members WHERE class_id = '.$row['id'].' AND sub <> 11 OR class_id2 = '.$row['id'].' AND sub <> 11 ORDER BY usr ASC';
                                                                            $res = mysql_query($sql);
                                                                            while($sub = mysql_fetch_array($res)) {
                                                                                $p1 = '';
                                                                                if($row['parent1'] == $sub['id']) {
                                                                                    $p1 = 'selected';
                                                                                }
                                                                                echo '<option value="'.$sub['id'].'" '.$p1.'>'.mb_substr($sub['fname'], 0, 1, 'utf-8').'.&nbsp;'.$sub['lname'].'</option>';
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                    <label class="span3">Parent 2:</label>
                                                                    <select name="parent2" class="span3">
                                                                        <option value="0">Select user:</option>
                                                                        <?php
                                                                            $sql = 'SELECT id, fname, lname, class_id FROM members WHERE class_id = '.$row['id'].' AND sub <> 11 OR class_id2 = '.$row['id'].' AND sub <> 11 ORDER BY usr ASC';
                                                                            $res = mysql_query($sql);
                                                                            while($sub = mysql_fetch_array($res)) {
                                                                                $p2 = '';
                                                                                if($row['parent2'] == $sub['id']) {
                                                                                    $p2 = 'selected';
                                                                                }
                                                                                echo '<option value="'.$sub['id'].'" '.$p2.'>'.mb_substr($sub['fname'], 0, 1, 'utf-8').'.&nbsp;'.$sub['lname'].'</option>';
                                                                            }
                                                                        ?>
                                                                    </select>

                                                            </div> <!-- /modal-body -->

                                                            <div class="modal-footer">
                                                                    <input type="hidden" name="classid" value="<?=$row['id'];?>">
                                                                    <input type="submit" name="submit" value="Update" class="btn btn-primary">
                                                                    <?php
                                                                        if($access == 1){
                                                                            echo '<input type="submit" name="submit" value="Delete" class="btn btn-danger">';
                                                                        }
                                                                    ?>
                                                                </form>
                                                            </div> <!-- /modal-footer -->
                                                        </div> <!-- /editclass -->
                                                <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /tab-pane classrooms -->

                                    <div class="tab-pane" id="categories">

                                        <span class="pull-right"><a rel="tooltip" data-placement="top" href="#addcat" data-original-title="Add Category" data-toggle="modal"><i class="icon-plus"></i><span class="alternative-font">&nbsp;Add Category</span></a></span>
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Category</th>
                                                <th>Organizations</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    $b = 0;
                                                    while($row = mysql_fetch_array($catres)) {
                                                        $b++;
                                                        $sql = 'SELECT org.id AS orgid, cat_id, name FROM org LEFT JOIN cat ON org.cat_id = cat.id WHERE cat_id = '.$row['id'].' ORDER BY name ASC';
                                                        $res = mysql_query($sql);
                                                        $mem = mysql_num_rows($res);
                                                        echo '<tr>';
                                                        echo '    <td><a href="#editcat'.$b.'" data-toggle="modal">'.$row['title'].'</a></td>';
                                                        echo '    <td><a href="#orgsincat'.$b.'" data-toggle="modal">'.$mem.'</a></td>';
                                                        echo '</tr>';
                                                ?>
                                                    <!-- editcat modal -->
                                                        <div id="editcat<?=$b;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="editcat<?=$b;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="editcat<?=$b;?>Label">Edit Details for: <?=$row['title'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <form action="" method="post">
                                                                    <label class="span3">Category Title:</label>
                                                                    <input type="text" name="title" value="<?=$row['title'];?>" maxlength="50" class="span3">
                                                            </div> <!-- /modal-body -->

                                                            <div class="modal-footer">
                                                                    <input type="hidden" name="catid" value="<?=$row['id'];?>">
                                                                    <input type="submit" name="submit" value="Update Category" class="btn btn-primary">
                                                                    <?php
                                                                        if($access == 1){
                                                                            echo '<input type="submit" name="submit" value="Delete" class="btn btn-danger">';
                                                                        }
                                                                    ?>
                                                                </form>
                                                            </div> <!-- /modal-footer -->
                                                        </div> <!-- /editcat -->

                                                    <!-- orgsincat modal -->
                                                        <div id="orgsincat<?=$b;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="orgsincat<?=$b;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="orgsincat<?=$b;?>Label">Organizations in: <?=$row['title'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <p>
                                                                <?php
                                                                    while($orgs = mysql_fetch_array($res)) {
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
                                    </div> <!-- /tab-pane categories -->

                                    <div class="tab-pane" id="bfg">

                                        <table class="table table-striped">
                                            <thead>
                                                <th>Category</th>
                                                <th>Organizations</th>
                                            </thead>

                                            <tbody>
                                                <?php
                                                    $b = 0;
                                                    while($row = mysql_fetch_array($catres)) {
                                                        $b++;
                                                        $sql = 'SELECT org.id AS orgid, cat_id, name FROM org LEFT JOIN cat ON org.cat_id = cat.id WHERE cat_id = '.$row['id'].' ORDER BY name ASC';
                                                        $res = mysql_query($sql);
                                                        $mem = mysql_num_rows($res);
                                                        echo '<tr>';
                                                        echo '    <td><a href="#editcat'.$b.'" data-toggle="modal">'.$row['title'].'</a></td>';
                                                        echo '    <td><a href="#orgsincat'.$b.'" data-toggle="modal">'.$mem.'</a></td>';
                                                        echo '</tr>';
                                                ?>
                                                    <!-- editcat modal -->
                                                        <div id="editcat<?=$b;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="editcat<?=$b;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="editcat<?=$b;?>Label">Edit Details for: <?=$row['title'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <form action="" method="post">
                                                                    <label class="span3">Category Title:</label>
                                                                    <input type="text" name="title" value="<?=$row['title'];?>" maxlength="50" class="span3">
                                                            </div> <!-- /modal-body -->

                                                            <div class="modal-footer">
                                                                    <input type="hidden" name="catid" value="<?=$row['id'];?>">
                                                                    <input type="submit" name="submit" value="Update Category" class="btn btn-primary">
                                                                    <?php
                                                                        if($access == 1){
                                                                            echo '<input type="submit" name="submit" value="Delete" class="btn btn-danger">';
                                                                        }
                                                                    ?>
                                                                </form>
                                                            </div> <!-- /modal-footer -->
                                                        </div> <!-- /editcat -->

                                                    <!-- orgsincat modal -->
                                                        <div id="orgsincat<?=$b;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="orgsincat<?=$b;?>Label" aria-hidden="true">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                <h3 id="orgsincat<?=$b;?>Label">Organizations in: <?=$row['title'];?></h3>
                                                            </div> <!-- /modal-header -->

                                                            <div class="modal-body">
                                                                <p>
                                                                <?php
                                                                    while($orgs = mysql_fetch_array($res)) {
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
                                    </div> <!-- /tab-pane bfg -->

                                </div> <!-- /tab-content -->
                            </div> <!-- /tabs -->

                        </div> <!-- /span12 -->
                    </div> <!-- /row -->
                </div> <!-- /container -->
            </div> <!-- /main -->

<!-- addcat modal -->
<div id="addcat" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addcatLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="addcatLabel">Add New Category</h3>
    </div>
    <div class="modal-body">
        <form action="" method="post">
            <label class="span3">Title:</label>
            <input type="text" name="title" class="span3">
    </div>
    <div class="modal-footer">
            <input type="submit" name="submit" value="Add Category" class="btn btn-primary">
        </form>
    </div>
</div> <!-- /additem -->
<?php
    include('inc/footer.php');
?>