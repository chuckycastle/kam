<?php
//smoke and mirrors
    include('inc/head.php');
    
//set access level
    if(!isset($_SESSION['acc'])) {
        $access = 5;
    } else {
        $access = $_SESSION['acc'];
    } 

//SQL
    $sval = $_GET['q'];
    //select matching organizations
    $sqry = 'SELECT org.id AS orgid, cat_id, name, poc_name, poc_phone, poc_email FROM org LEFT JOIN cat ON org.cat_id = cat.id LEFT JOIN items ON items.org_id = org.id WHERE org.name LIKE \'%'.$sval.'%\' OR cat.title LIKE \'%'.$sval.'%\' OR items.desc LIKE \'%'.$sval.'%\' ORDER BY org.name ASC';
    $sres = mysql_query($sqry);

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
                                    <li class="active">Search</li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Krieger Auction Manager    </h2>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                    </div> <!-- /container -->
                </section>

                <div class="container">
                    <div class="row">
                        <div class="span12">
                            <p><?php
                                    $count = mysql_num_rows($sres);
                                    if($count > 1 or $count == 0) {
                                        $plural = 's';
                                    }
                                    echo $count.' result'.$plural.' for <strong>'.$sval.'</strong>:';
                                ?>
                            </p>
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Organization</th>
                                                <th>POC</th>
                                                <th>Phone #</th>
                                                <th>Email</th>
                                            </thead>
                                            
                                            <tbody>
                                                <?php
                                                    while($row = mysql_fetch_array($sres)) {
                                                        switch($row['donate']){
                                                            case 1:
                                                                $icon = 'thumbs-up';
                                                                break;
                                                            case 2:
                                                                $icon = 'thumbs-down';
                                                                break;
                                                            default:
                                                                $icon = '';
                                                        }
                                                        echo '<tr>';
                                                        echo '    <td><i class="icon-'.$icon.'"></i>&nbsp;<a href="orginfo.php?orgid='.$row['orgid'].'">'.$row['name'].'</a></td>';
                                                        echo '    <td>'.$row['poc_name'].'</td>';
                                                        echo '    <td>'.$row['poc_phone'].'</td>';
                                                        echo '    <td>'.$row['poc_email'].'</td>';
                                                        echo '</tr>';
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                    </div>
                    
                    <div class="row">
                        <div class="span12">

                        </div>
                    </div>
                </div>

            </div>

<?php
    include('inc/footer.php');
?>