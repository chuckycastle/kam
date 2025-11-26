<?php
/* Database config */

$db_host        = 'kriegerdb.db.10424383.hostedresource.com';
$db_user        = 'kriegerdb';
$db_pass        = 'kr13G3R@@';
$db_database    = 'kriegerdb';

/* End config */

function formatPhone($num)
{
$num = preg_replace('/[^0-9]/', '', $num);

$len = strlen($num);
if($len == 7)
$num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
elseif($len == 10)
$num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);

return $num;
}

$link = mysql_connect($db_host,$db_user,$db_pass) or die('Unable to establish a DB connection');

mysql_select_db($db_database,$link);
mysql_query("SET names UTF8");

    //select proper info from msg table
    /* $newqry = 'SELECT COUNT(*) AS new, usr_id, members.email FROM msg INNER JOIN members ON usr_id=members.id GROUP BY usr_id';
    $newres = mysql_query($newqry); */

    //select data from CSV table
    /* $qry = 'SELECT * FROM CSV';
    $res = mysql_query($qry);

    while($row = mysql_fetch_array($res)) {
        mysql_query("INSERT INTO org(id,name,poc_name,poc_email,poc_phone,url,avail,org.desc,usr_id,dt,donate)
                        VALUES(
                            '',
                            '".$row['company']."',
                            '',
                            '',
                            '',
                            '',
                            1,
                            '".$row['desc']."',
                            '',
                            now(),
                            0
                        )");
    }

    echo 'done.'; */

    /* $qry = 'SELECT * FROM org WHERE poc_phone IS NOT NULL';
    $res = mysql_query($qry);

    while($row = mysql_fetch_array($res)) {
        $phone = formatPhone($row['poc_phone']);
        mysql_query('UPDATE org SET poc_phone = "'.$phone.'"');
    }

    echo 'done.'; */

    /*//select proper info from org table
    $sql = 'SELECT id, poc_phone FROM org WHERE poc_phone IS NOT NULL';
    $res = mysql_query($sql);

    while($row = mysql_fetch_array($res)) {
        $phone = $row['poc_phone'];
        $phone = preg_replace("/[^0-9]/","", $phone);
        $phone = formatPhone($phone);
        $qry = 'UPDATE org SET poc_phone = "'.$phone.'" WHERE id = '.$row['id'].' LIMIT 1';
        $exe = mysql_query($qry);
    }*/

    /*$qry = 'SELECT org.id, org.name, google.id, google.ORG AS google, cat.id AS catid, google.CAT FROM org, google LEFT JOIN cat ON cat.title LIKE google.CAT WHERE org.name LIKE google.ORG';
    $res = mysql_query($qry);

    while($row = mysql_fetch_array($res)){
        $sql = 'UPDATE org SET cat_id = '.$row['catid'].' WHERE org.name LIKE "'.$row['google'].'" LIMIT 1';
        $run = mysql_query($sql);
    }*/

?>