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
                                    <li><a href="index.php">Home</a></li>
                                    <li class="active"><a href="help.php">Help</a></li>
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
                    <div class="span12">
                        hello world.
                    </div>
                </div>
            </div>

<?php
    include('inc/footer.php');
?>