<?php
//smoke and mirrors
    include('inc/head.php');

//set access level
    if(!isset($_SESSION['acc'])) {
        $access = 5;
    } else {
        $access = $_SESSION['acc'];
    }

//include html header
    include('inc/header.php');
?>
            <div role="main" class="main">

                <section class="page-top">
                    <div class="container">
                        <div class="row">
                            <div class="span12">
                                <ul class="breadcrumb">
                                    <li class="active">Home</li>
                                </ul>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                        <div class="row">
                            <div class="span12">
                                <h2>Krieger Auction Manager</h2>
                            </div> <!-- /span12 -->
                        </div> <!-- /row -->
                    </div> <!-- /container -->
                </section>

                <div class="home-concept">
                    <div class="container">
                            <div class="row">
                                <h2 class="short">
                                    Welcome to the Krieger Auction Manager website
                                </h2>
                                <!-- <p class="featured lead">
                                    The Spring Auction starts on <span class="alternative-font" data-appear-animation="tada">April 23, 2014</span>.
                                    To reach our goal of raising <b>$20,000</b> for the center, we need to bring in <b>$40,000</b> worth of donations.
                                </p> -->
                                <p class="featured lead">
                                    You guys totally <span class="alternative-font" data-appear-animation="tada">rock!</span>  Thanks to you, we exceeded our <b>$40,000</b> solicitation goal!
                                    The auction is now live at <a href="http://biddingforgood.com/krieger">Bidding For Good</a>.  Good luck and happy bidding!
                                </p>
                            </div>
                            <div class="row">
                            <h2>Our <b>Features</b></h2>
                                <div class="row">
                                    <div class="span6">
                                        <div class="feature-box">
                                            <div class="feature-box-icon">
                                                <i class="icon icon-bar-chart"></i>
                                            </div>
                                            <div class="feature-box-info">
                                                <h4 class="shorter"><?php if($access!=5){ echo '<a href="manage.php?tab=mine">';} ?>Tracking<?php if($access!=5){ echo '</a>';} ?></h4>
                                                <p class="tall">Keep track of businesses that you've contacted about making a donation to our 2014 Spring Auction.</p>
                                            </div>
                                        </div>
                                        <div class="feature-box">
                                            <div class="feature-box-icon">
                                                <i class="icon icon-archive"></i>
                                            </div>
                                            <div class="feature-box-info">
                                                <h4 class="shorter"><?php if($access!=5){ echo '<a href="solicit.php">';} ?>Database<?php if($access!=5){ echo '</a>';} ?></h4>
                                                <p class="tall">Choose from a list of previous donors as an easy way to start soliciting for this year.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span6">
                                        <div class="feature-box">
                                            <div class="feature-box-icon">
                                                <i class="icon icon-flag-checkered"></i>
                                            </div>
                                            <div class="feature-box-info">
                                                <h4 class="shorter"><?php if($access!=5){ echo '<a href="reports.php?tab=class">';} ?>Classroom Competition<?php if($access!=5){ echo '</a>';} ?></h4>
                                                <p class="tall">See which groups are currently ahead in the Classroom Competition.</p>
                                            </div>
                                        </div>
                                        <div class="feature-box">
                                            <div class="feature-box-icon">
                                                <i class="icon icon-cloud"></i>
                                            </div>
                                            <div class="feature-box-info">
                                                <h4 class="shorter"><?php if($access!=5){ echo '<a href="/docs">';} ?>Document Cloud<?php if($access!=5){ echo '</a>';} ?></h4>
                                                <p class="tall">Access all Auction related documents, including the donation letter and form.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                                if(!isset($_SESSION['acc'])){
                                    echo '<div class="row">';
                                    echo '<div class="span12">';
                                    echo '<p>Ready to get started?  Click <a href="#login" data-toggle="modal"><span class="alternative-font">Login</span></a> to register an account!</p>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            ?>
                    </div>
                </div>
            </div>

<?php
    include('inc/footer.php');
?>