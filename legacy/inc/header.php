<!DOCTYPE html>
<!--[if IE 8]>            <html class="ie ie8"> <![endif]-->
<!--[if IE 9]>            <html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!-->    <html> <!--<![endif]-->
    <head>

        <!-- Basic -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>UCLA ECE Krieger Auction Manager</title>
        <meta name="keywords" content="UCLA, ECE, Krieger, auction, manager" />
        <meta name="description" content="UCLA ECE Krieger Auction Manager">
        <meta name="author" content="chuckcastle.me">

        <!-- Mobile Metas
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->

        <!-- Web Fonts  -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

        <!-- Libs CSS -->
        <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="stylesheet" href="css/fonts/font-awesome/css/font-awesome.css">
        <!-- <link rel="stylesheet" href="vendor/flexslider/flexslider.css" media="screen" />
        <link rel="stylesheet" href="vendor/magnific-popup/magnific-popup.css" media="screen" /> -->

        <!-- Theme CSS -->
        <link rel="stylesheet" href="css/theme.css">
        <link rel="stylesheet" href="css/theme-blog.css">
        <link rel="stylesheet" href="css/theme-elements.css">
        <link rel="stylesheet" href="css/theme-animate.css">

        <!-- Custom CSS -->
        <link rel="stylesheet" href="css/tablecloth.css">
        <link rel="stylesheet" href="css/bootstrap-tables.css">

        <!-- Skin CSS -->
        <link rel="stylesheet" href="css/skins/blue.css">

        <!-- Favicons -->
        <link rel="shortcut icon" href="img/favicon.ico">
        <link rel="apple-touch-icon" href="img/apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="72x72" href="img/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="114x114" href="img/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="144x144" href="img/apple-touch-icon-144x144.png">

        <!-- Head Libs -->
        <script src="vendor/modernizr.js"></script>

        <!--[if IE]>
            <link rel="stylesheet" href="css/ie.css">
        <![endif]-->

        <!--[if lte IE 8]>
            <script src="vendor/respond.js"></script>
        <![endif]-->

    </head>

    <body class="boxed">

    <?php
        //alert error
        if($_SESSION['msg']['error']) {
            echo '<div class="alert alert-block alert-error bounce">';
            echo '<button data-dismiss="alert" class="close" type="button">×</button>';
            echo '<h4 class="alert-heading">'.$_SESSION['msg']['error'].'</h4>';
            unset($_SESSION['msg']['error']);
            echo '</div>';
        }

        //alert success
        if($_SESSION['msg']['success']) {
            echo '<div class="alert alert-block alert-success bounce">';
            echo '<button data-dismiss="alert" class="close" type="button">×</button>';
            echo '<h4 class="alert-heading">'.$_SESSION['msg']['success'].'</h4>';
            unset($_SESSION['msg']['success']);
            echo '</div>';
        }

        //alert warning
        if($_SESSION['msg']['warning']) {
            echo '<div class="alert alert-block alert bounce">';
            echo '<button data-dismiss="alert" class="close" type="button">×</button>';
            echo '<h4 class="alert-heading">'.$_SESSION['msg']['warning'].'</h4>';
            unset($_SESSION['msg']['warning']);
            echo '</div>';
        }

        //alert info
        if($_SESSION['msg']['info']) {
            echo '<div class="alert alert-block alert-info bounce">';
            echo '<button data-dismiss="alert" class="close" type="button">×</button>';
            echo '<h4 class="alert-heading">'.$_SESSION['msg']['info'].'</h4>';
            unset($_SESSION['msg']['info']);
            echo '</div>';
        }

    ?>

        <div class="body">
            <header>
                <div class="container">
                    <h1 class="logo">
                        <a href="index.php">
                            <img alt="Krieger Auction" src="img/spring_auction_logo.png">
                        </a>
                    </h1>

                    <!-- Search -->
                    <div class="search">
                        <form class="form-search" id="searchForm" action="search.php" method="get">
                            <div class="control-group">
                                <input type="text" class="input-medium search-query" name="q" id="q" placeholder="Search business or category...">
                                <span class="input-group-btn">
                                    <button class="search" type="submit"><i class="icon-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>

                    <!-- Navigation -->
                    <nav>
                        <ul class="nav nav-pills nav-top">
                            <li>
                                <a href="index.php"><i class="icon-angle-right"></i>Home</a>
                            </li>
                            <?php
                                if(!$_SESSION['id']){
                            ?>
                            <li>
                                <a href="#login" data-toggle="modal"><i class="icon-angle-right"></i>Login</a>
                                <div id="login" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="loginLabel" aria-hidden="true">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 id="loginLabel">Welcome to KAM</h3>
                                    </div> <!-- /modal-header -->

                                    <div class="modal-body">
                                        <div class="tabs">
                                            <ul class="nav nav-tabs">
                                                <li class="active"><a href="#login-tab" data-toggle="tab">Login</a></li>
                                                <li><a href="#register-tab" data-toggle="tab">Register</a></li>
                                            </ul>

                                            <div class="tab-content">
                                                <div class="tab-pane active" id="login-tab">
                                                    <form action="" method="post">
                                                        <div class="row controls">
                                                            <div class="span3 control-group">
                                                                <label>Username</label>
                                                                <input type="text" name="username" id="username" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label>Password</label>
                                                                <input type="password" name="password" id="password" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group pull-right">
                                                                <p><a href="#forgotpw" data-toggle="tab">Forgot Password?</a></p>
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <input type="submit" name="submit" value="Login" class="btn btn-primary">
                                                            </div>
                                                        </div>
                                                    </form> <!-- /login form -->
                                                </div> <!-- /login tab -->

                                                <div class="tab-pane" id="forgotpw">
                                                    <form action="" method="post">
                                                        <div class="row controls">
                                                            <div class="span3 control-group">
                                                                <label>Username</label>
                                                                <input type="text" name="username" id="username" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label>Email</label>
                                                                <input type="text" name="email" id="email" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <input type="submit" name="submit" value="Reset" class="btn btn-primary">
                                                            </div>
                                                        </div>
                                                    </form> <!-- /reset form -->
                                                </div> <!-- /forgotpw tab -->

                                                <div class="tab-pane" id="register-tab">
                                                    <form action="" method="post">
                                                        <div class="row controls">
                                                            <div class="span3 control-group">
                                                                <label>First Name</label>
                                                                <input type="text" name="fname" id="fname" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label>Last Name</label>
                                                                <input type="text" name="lname" id="lname" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label>Username</label>
                                                                <input type="text" name="username" id="username" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label>Email</label>
                                                                <input type="text" name="email" id="email" value="" maxlength="100" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label>Password</label>
                                                                <input type="password" name="pass" id="pass" value="" maxlength="20" class="span3" />
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <label id="lblcheckpass">Verify Password</label>
                                                                <input type="password" name="vpass" id="vpass" value="" maxlength="20" class="span3" onchange="checkPass();"/>
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <select name="class" class="span3">
                                                                    <option value="0">Select Classroom 1:</option>
                                                                    <option value="0"></option>
                                                                    <?php
                                                                        $sql = 'SELECT * FROM class ORDER BY name ASC';
                                                                        $res = mysql_query($sql);
                                                                        while($row = mysql_fetch_array($res)) {
                                                                            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                                        }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="span3 control-group">
                                                                <select name="class2" class="span3">
                                                                    <option value="0" selected>Select Classroom 2:</option>
                                                                    <option value="0"></option>
                                                                    <?php
                                                                        $sql2 = 'SELECT * FROM class ORDER BY name ASC';
                                                                        $res2 = mysql_query($sql2);
                                                                        while($row2 = mysql_fetch_array($res2)) {
                                                                            echo '<option value="'.$row2['id'].'">'.$row2['name'].'</option>';
                                                                        }
                                                                    ?>
                                                                </select>
                                                                <input type="submit" name="submit" value="Register" class="btn btn-primary">
                                                            </div>
                                                        </div>
                                                    </form> <!-- /register form -->
                                                </div> <!-- /register tab -->
                                            </div> <!-- /tab-content -->
                                        </div> <!-- /tabs -->
                                    </div> <!-- /modal-body -->
                                </div> <!-- /modal -->
                            </li>
                        <?php
                            } else {
                        ?>
                            <li>
                                <a href="#logout" data-toggle="modal"><i class="icon-angle-right"></i>Logout</a>
                                <div id="logout" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="logoutHead" aria-hidden="true">
                                    <div class="modal-body">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 id="logoutHead">Logout</h3>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to log out?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="submit" name="submit" value="Logoff" class="btn btn-success" onclick="location.href='index.php?logoff';">
                                    </div>
                                </div>
                            </li>

                            <span class="span1 pull-right">
                                <a rel="tooltip" href="manage.php#account" data-original-title="Logged in as <?=$_SESSION['usr'].' (Access level: '.$_SESSION['acc'].')';?>"><i class="icon-user icon-large"></i></a>
                                <?php
                                    //select proper info from msg table
                                    $newqry = 'SELECT * FROM msg WHERE usr_id = '.$_SESSION['id'].' AND new = 0';
                                    $newres = mysql_query($newqry);
                                    $new = mysql_num_rows($newres);

                                    if($new == 0){
                                        $badge = '';
                                    } else {
                                        $badge = 'badge-success';
                                    }
                                        echo '<a href="manage.php#messages"><span class="badge '.$badge.'">'.$new.'</span></a>';
                                ?>
                            </span>
                        <?php
                            }
                        ?>
                        </ul>
                    </nav>

                    <?php
                        if(isset($_SESSION['usr'])){
                    ?>
                    <nav>
                        <ul class="nav nav-pills nav-main" id="mainMenu">
                            <li>
                                <a href="solicit.php">Solicit</a>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" href="manage.php">
                                    Manage
                                    <i class="icon-angle-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="manage.php#organization">Organizations</a></li>
                                    <li><a href="manage.php#mine">My Assignments</a></li>
                                    <li><a href="manage.php#account">Account</a></li>
                                    <li><a href="manage.php#messages">Notifications</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" href="reports.php">
                                    Reports
                                    <i class="icon-angle-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="reports.php#classldr">Classroom Leaderboard</a></li>
                                    <li><a href="reports.php#parentldr">Parent Leaderboard</a></li>
                                    <?php
                                        if($_SESSION['acc'] < 3) {
                                            echo '<li><a href="reports.php#itemsrcv">Items Received</a></li>';
                                            echo '<li><a href="reports.php#donateyes">Will Donate</a></li>';
                                            echo '<li><a href="reports.php#donateno">Will Not Donate</a></li>';
                                        }
                                    ?>
                                </ul>
                            </li>
                            <?php
                                if($_SESSION['acc'] < 3) {
                            ?>
                            <li class="dropdown">
                                <a class="dropdown-toggle" href="admin.php">
                                    Admin
                                    <i class="icon-angle-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="admin.php#classrooms">Classrooms</a></li>
                                    <li><a href="admin.php#categories">Categories</a></li>
                                    <?php
                                        if($_SESSION['acc']==1){
                                            echo '<li><a href="admin.php#bfg">BFG Export</a></li>';
                                        }
                                    ?>
                                </ul>
                            </li>
                            <?php
                                }
                            ?>

                    <?php
                        }
                    ?>
                        </ul>
                    </nav>
                </div>
            </header>