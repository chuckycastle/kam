<?php
//smoke and mirrors
    include('inc/head.php');

//set access level
    if(!isset($_SESSION['acc'])) {
        $access = 5;
    } else {
        $access = $_SESSION['acc'];
    }

//SQL & pagination
    $adjacents = 4;
    $query = 'SELECT COUNT(*) FROM org WHERE avail = 1';
    $total_items = mysql_fetch_array(mysql_query($query));

    $targetpage = "solicit.php";
    $limit = 20;
    if(isset($_GET['page'])) {
        $page = $_GET['page'];
        $start = ($page - 1) * $limit;
    } else {
        $page = 0;
        $start = 0;
    }

    //get data
    $sql = 'SELECT * FROM org WHERE avail = 1 LIMIT '.$start.', '.$limit;
    $result = mysql_query($sql);

    //setup page vars for display
    if ($page == 0) $page = 1;
    $prev = $page - 1;
    $next = $page + 1;
    $lastpage = ceil($total_items[0]/$limit);
    $lpm1 = $lastpage - 1;

    $pagination = "";
    if($lastpage > 1) {
        $pagination .= '<div class="pagination pagination-large pagination-center"><ul>';

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
                                    <li class="active">Solicit</li>
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
                    <div class="row center">
                        <?php
                            //donation goal
                            $goal = 40000;

                            //select total amount donation from items table
                            $perqry = 'SELECT SUM(items.value) AS total FROM items';
                            $perres = mysql_query($perqry);

                            while($row = mysql_fetch_array($perres)){
                                $total = $row['total'];
                                //percentage of goal
                                $percent = floor(($total/$goal)*100);
                            }

                            //format as currency
                            $total = number_format($total, 2, '.', ',');
                            $goal = number_format($goal, 2, '.', ',');
                        ?>
                        <h2 class="short">We've solicited $<?=$total;?> (<?=$percent;?>%) of our $<?=$goal;?> goal!</h2>
                        <div class="progress progress-striped active">
                            <div class="bar" data-appear-progress-animation="<?php echo $percent; ?>%" style="width: <?php echo $percent; ?>%;">
                                <span class="pull-right"><?php echo $percent; ?>%</span>
                            </div>
                        </div>
                    </div> <!-- /row center -->

                    <div class="span12">
                        <?php
                            if($access==5){
                        ?>
                        <p class="lead">
                            Welcome to the Krieger Auction Manager website. The Spring Auction starts on April 23, 2014.
                            To reach our goal of raising $20,000 for the center, we need to bring in $40,000 worth of donations.
                            And we need all parents to participate to make this happen! This new auction manager will allow Krieger families to:

                            <ul>
                                <li>Keep track of businesses that you've contacted about making a donation to our 2014 Spring Auction</li>
                                <li>Choose from a list of previous donors as an easy way to start soliciting for this year</li>
                                <li>See which groups are currently ahead in the Classroom Competition</li>
                                <li>Access all Auction related documents, including the donation letter and form</li>
                            </ul>
                        </p>
                        <p class="lead">
                            Ready to get started?  Click <a href="#login" data-toggle="modal">Login</a> above to register an account!
                        </p>
                        <?php
                            } else {
                        ?>
                        <p class="featured lead">
                            Not sure who to contact?  These are some of the businesses that have donated in the past:
                        </p>

                        <p class="featured lead">
                            Don't see a business that you'd like to solicit from?  Send a <a href="manage.php#messages">message</a> to R. Castillo, J. Sorenson, or S. Ervin!
                        </p>
                        <?php
                            echo $pagination;
                        ?>

                        <table class="table table-striped">
                            <thead>
                                <th>Organization</th>
                                <th>POC</th>
                                <th>Phone #</th>
                                <th>Email</th>
                            </thead>

                            <tbody>
                                <?php
                                    while($row = mysql_fetch_array($result)) {
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

                                        $orgname = (strlen($row['name']) > 23) ? substr($row['name'],0,20).'...' : $row['name'];
                                        echo '<tr>';
                                        echo '    <td><i class="icon-'.$icon.'"></i>&nbsp;<a href="orginfo.php?orgid='.$row['id'].'" title="'.$row['name'].'">'.$orgname.'</a></td>';
                                        echo '    <td>'.$row['poc_name'].'</td>';
                                        echo '    <td>'.$row['poc_phone'].'</td>';
                                        echo '    <td>'.$row['poc_email'].'</td>';
                                        echo '    <td>'.$row['usr'].'</td>';
                                        echo '</tr>';
                                    }

                                echo '</tbody>';
                                echo '</table>';
                                echo $pagination;
                            }
                            ?>
                    </div>
                </div>
            </div>

<?php
//include html footer
    include('inc/footer.php');
?>