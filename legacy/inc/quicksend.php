<?php
/* Database config */

$db_host        = 'kriegerdb.db.10424383.hostedresource.com';
$db_user        = 'kriegerdb';
$db_pass        = 'kr13G3R@@';
$db_database    = 'kriegerdb';

/* End config */



$link = mysql_connect($db_host,$db_user,$db_pass) or die('Unable to establish a DB connection');

mysql_select_db($db_database,$link);
mysql_query("SET names UTF8");

function send_mail($from,$to,$subject,$body)
{
    $headers = '';
    $headers .= "From: $from\n";
    $headers .= "Reply-to: $from\n";
    $headers .= "Return-Path: $from\n";
    $headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
    $headers .= "Date: " . date('r', time()) . "\n";

    mail($to,$subject,$body,$headers);
}

/*    //select YES donate from org table
    $yesqry = 'SELECT * FROM org WHERE donate = 1 ORDER BY name ASC';
    $yesres = mysql_query($yesqry);
    while($row = mysql_fetch_array($yesres)) {
        $itmqry = 'SELECT * FROM items WHERE org_id = '.$row['id'].' AND received = 1';
        $itmres = mysql_query($itmqry);
        $items = mysql_num_rows($itmres);
        if($items==0) {
            $sendqry = 'SELECT members.email, members.fname, org.name FROM members JOIN org ON org.usr_id = members.id WHERE org.id = '.$row['id'];
            $sendres = mysql_query($sendqry);
            $send = mysql_fetch_array($sendres);
            send_mail(  'donotreply@kriegercenter.org',
                        $send['email'],
                        'Krieger Auction Manager: Pending receipt of donation',
                        'Hey, '.$send['fname'].'! Thanks for soliciting from '.$send['name'].'. Don\'t forget to follow up with them as we have not received their donation.  Thank you for helping make our event a success!'
            );
            echo 'Mail sent to '.$send['email'].'<br />';
        } else {
            $donothing = 1;
        }
    } */

/*    //heartbleed update
    $hbqry = 'SELECT fname, email FROM members WHERE email <> "" GROUP BY email';
    $hbres = mysql_query($hbqry);
    while($hb = mysql_fetch_array($hbres)) {
        send_mail(  'donotereply@kriegercenter.org',
                    $hb['email'],
                    'Krieger Auction Manager Security Update',
                    'Howdy '.$hb['fname'].','."\n".'You\'ve probably heard about that nasty Heartbleed bug that\'t been going around.  We just want to let you know that KAM is not vulnerable nor is it compromised by the bug!  Why?  Because KAM\' super awesome and built by ninjas!?  Sadly, no.  The reason your email and password are safe with KAM is much more anticlimactic: KAM doesn\'t use OpenSSL.'."\n\n".'We do, however, encourage you to read up on Heartbleed and familiarize yourself with what to do over the next couple weeks.  You can familiarize yourself with the bug via Mashable at http://mashable.com/2014/04/09/heartbleed-what-to-do/.  Of course, for the geekily inclined, TechCrunch has a good article (and video!) at http://techcrunch.com/2014/04/08/what-is-heartbleed-the-video/.'."\n\n".'Again, KAM is completely unaffected by the Heartbleed bug.  Thank you for your continued support as we draw closer to the April 23rd online auction and May 9th live auction!'
        );
        echo 'Mail sent to '.$hb['email'].'<br />';
    } */

/*
    //eom thank yous
    $tyqry = 'SELECT org.poc_name, org.poc_email, org.name, members.fname, members.lname, items.desc FROM org JOIN members ON members.id = org.usr_id JOIN items ON items.org_id = org.id';
    $tyres = mysql_query($tyqry);
    while($ty = mysql_fetch_array($tyres)){
        echo 'Send email to: '.$ty['poc_email'].'<br />';
        echo 'Hey '.$ty['poc_name'].', <br />';
        echo 'Thank you so much for '.$ty['name'].'\'s donation of '.$ty['desc'].'.  Because of your generosity, '.$ty['fname'].' '.$ty['lname'].' was able to make a significant contribution to our school!<br />';
        echo '<br />We look forward to your participation next year :)<br>';
        echo '<br />Sincerely,<br>Krieger<br /><br />';
    }
*/

/*
//email home stretch!
    $prtldrqry = 'SELECT members.opt_out, members.fname, members.lname, members.email, SUM(items.value) AS total FROM org JOIN members ON org.usr_id = members.id JOIN items ON items.org_id = org.id WHERE members.opt_out = "0" GROUP BY members.usr ORDER BY total DESC';
    $prtldrres = mysql_query($prtldrqry);

    while($ldr = mysql_fetch_array($prtldrres)){
        send_mail(    'donotreply@kriegercenter.org',
        $ldr['email'],
        'Krieger Auction Manager - We\'re on the home stretch!',
        'Hey '.$ldr['fname'].','."\n".'Guess what!?  Because of your hard work we have exceeded our initial goal of $40,000!!!  You helped us raise $'.$ldr['total'].' towards this goal with your awesomeness!'."\n\n".'There\'s still time to get those last minute solicitations in.  C\'mon, you know you wanna help make this record-breaking solicitation year the bestest ever!  One more solicitation!  One more solicitation!  One more solicitation!'."\n\n".'--'."\n".'We\'re so excited that we don\'t mind if you reply to this email... do it... you know you wanna...');
    }
*/
?>