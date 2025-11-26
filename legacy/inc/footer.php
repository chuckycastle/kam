            <footer class="short">
                <div class="footer-copyright">
                    <div class="container">
                        <div class="row">
                            <div class="span1">
                                <a href="index.php" class="logo">
                                    <img alt="Porto Website Template" src="img/spring_auction_logo_footer.png">
                                </a>
                            </div>
                            <div class="span7">
                                <p>Changing lives one diaper at a time&nbsp;|&nbsp;another site by <a href="http://www.chuckcastle.me">chuckcastle.me</a></p>
                            </div>
                            <div class="span4">
                                <nav id="sub-menu">
                                    <ul>
                                        <li><a href="index.php">Home</a></li>
                                        <?php
                                            if(isset($_SESSION['id'])) {
                                                echo '<li><a href="index.php?logoff">Logout</a></li>';
                                            } else {
                                                echo '<li><a href="#login" data-toggle="modal">Login</a></li>';
                                            }
                                        ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Libs -->
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="vendor/jquery.easing.js"></script>
        <script src="vendor/jquery.appear.js"></script>
        <script src="vendor/bootstrap.js"></script>
        <script src="vendor/selectnav.js"></script>
        <script src="vendor/jquery.tablesorter.js"></script>
        <script src="vendor/jquery.tablecloth.js"></script>
        <script src="vendor/magnific-popup/magnific-popup.js"></script>
        <script src="vendor/jquery.validate.js"></script>
        <script src="js/plugins.js"></script>

        <!-- Theme Initializer -->
        <script src="js/theme.js"></script>

        <!-- Custom JS -->
        <script src="js/custom.js"></script>

    </body>
</html>