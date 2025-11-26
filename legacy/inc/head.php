<?php
define('INCLUDE_CHECK',true);

//global requirements
require 'inc/connect.php';
require 'inc/functions.php';

//session config
session_start();

//logoff
if(isset($_GET['logoff']))
{
    $_SESSION = array();
    session_destroy();

    header("Location: index.php");
    exit;
}

//login
if($_POST['submit']=='Login')
{
    $err = array();

    if(!$_POST['username'] || !$_POST['password'])
        $err[] = 'Uh oh... You kinda gotta fill all the boxes in :/';

    if(!count($err))
    {
        $_POST['username'] = mysql_real_escape_string($_POST['username']);
        $_POST['password'] = mysql_real_escape_string($_POST['password']);

        $row = mysql_fetch_assoc(mysql_query("SELECT id,usr,acc FROM members WHERE usr='".$_POST['username']."' AND pass='".md5($_POST['password'])."'"));

        if($row['usr'])
        {
            $_SESSION['usr'] = $row['usr'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['acc'] = $row['acc'];
            $_SESSION['username'] = $row['usr'];

            $_SESSION['msg']['success']='Hey! You\'re now logged in as '.$_SESSION['usr'];

            $dt = date("Y-m-d H:i:s");

            mysql_query('UPDATE members SET lastlogin = "'.$dt.'" WHERE id = '.$_SESSION['id'].' LIMIT 1');
        }
        else $err[]='Aw snap! You entered a bad username and/or password :(';
    }

    if($err)
    $_SESSION['msg']['error'] = implode('<br />',$err);

    header("Location: solicit.php");
    exit;
}

//register
else if($_POST['submit']=='Register')
{
    $err = array();

    if(strlen($_POST['username'])<4 || strlen($_POST['username'])>12)
    {
        $err[]='Not so fast! Your username\'s gotta be between 4 and 12 characters!';
    }

    if($_POST['pass']!=$_POST['vpass'])
    {
        $err[]='Oops!  Passwords don\'t match!';
    }

    if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
    {
        $err[]='Whoops! Your username contains invalid characters!';
    }

    if(!checkEmail($_POST['email']))
    {
        $err[]='Wait a minute... that\'s not a valid email address...';
    }

    if(!count($err))
    {
        $pass = md5($_POST['vpass']);

        $_POST['email'] = mysql_real_escape_string($_POST['email']);
        $_POST['username'] = mysql_real_escape_string($_POST['username']);

        $p1qry = 'SELECT members.id, name, parent1 FROM class LEFT JOIN members ON members.id = class.parent1 WHERE class_id = '.$_POST['class'].' LIMIT 1';
        $p1res = mysql_query($p1qry);
        $p1rows = mysql_num_rows($p1res);

        $p2qry = 'SELECT members.id, name, parent2 FROM class LEFT JOIN members ON members.id = class.parent2 WHERE class_id = '.$_POST['class2'].' LIMIT 1';
        $p2res = mysql_query($p2qry);
        $p2rows = mysql_num_rows($p2res);

        if($p1rows == 0){
            $p1 = '11';
        } else {
            $fetch1 = mysql_fetch_array($p1res);
            $p1 = $fetch1['id'];
        }

        mysql_query("INSERT INTO msg(text,usr_id,new,dt)
                        VALUES(
                            '".$_POST['fname']." ".$_POST['lname']." registered an account.',
                            '".$p1."',
                            0,
                            now()
                        )");

        if($p2rows == 0){
            $p2 = '';
        } else {
            $fetch2 = mysql_fetch_array($p2res);
            $p2 = $fetch2['id'];
                if($p2 != $p1){
                    mysql_query("INSERT INTO msg(text,usr_id,new,dt)
                                VALUES(
                                '".$_POST['fname']." ".$_POST['lname']." registered an account.',
                                '".$p2."',
                                0,
                                now()
                                )");
                }
        }

        mysql_query("INSERT INTO members(fname,lname,usr,pass,email,regIP,dt,acc,assign,sub,class_id,class_id2)
                        VALUES(
                            '".nameize($_POST['fname'])."',
                            '".nameize($_POST['lname'])."',
                            '".$_POST['username']."',
                            '".$pass."',
                            '".$_POST['email']."',
                            '".$_SERVER['REMOTE_ADDR']."',
                            now(),
                            4,
                            1,
                            '".$p1."',
                            '".$_POST['class']."',
                            '".$_POST['class2']."'
                        )");

        if(mysql_affected_rows($link)==1)
        {
            send_mail(    'donotreply@kriegercenter.org',
                        $_POST['email'],
                        'Welcome to the Krieger Auction Manager!',
                        'Hey! Thanks for registering a new account.'."\n\n".'Your username is: '.$_POST['username']."\n".'Your password is: '.$_POST['vpass']."\n\n".'You can login at http://auction.kriegercenter.org'."\n\n".'--'."\n".'Please do not reply to this email.');

            $_SESSION['msg']['success']='Welcome to KAM!  You may now log in.  Your account info has been emailed to you for safe keeping :)';
        }
        else $err[]='Aw man! This username is already taken!';
    }

    if(count($err))
    {
        $_SESSION['msg']['error'] = implode('<br />',$err);
    }

    header("Location: index.php");
    exit;
}

//forgotpw
else if($_POST['submit']=='Reset')
{
    $err = array();

    if(strlen($_POST['username'])<4 || strlen($_POST['username'])>12)
    {
        $err[]='Not so fast! Your username\'s gotta be between 4 and 12 characters!';
    }

    if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
    {
        $err[]='Whoops! Your username contains invalid characters!';
    }

    if(!checkEmail($_POST['email']))
    {
        $err[]='Wait a minute... that\'s not a valid email address...';
    }

    if(!count($err))
    {
        if(isset($_POST['email'])) {
            $randompw = $_POST['username'];
            $hashedpw = md5($randompw);
            $mailfrom = $_POST['email'];
            $mailto = 'rosie.castillo@me.com';

            send_mail(  $mailfrom,
                        $mailto,
                        'KAM Password Reset',
                        'Hey Rosie, '.$_POST['username'].' has requested a password reset.'."\n\n".'The temporary password is: '.$randompw."\n".'The MD5 hash is: '.$hashedpw);

            $_SESSION['msg']['success']='Password reset request sent!';
        }
        else $err[]='Aw snap! You entered a bad username and/or email :(';
    }

    if($err)
    $_SESSION['msg']['error'] = implode('<br />',$err);

    header("Location: index.php");
    exit;
}

?>
