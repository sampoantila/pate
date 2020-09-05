<?php
session_start();

require_once 'CDb.php';

$db = new CDb();
$db->Connect();
$db->Table = 'modules';

$year = $_POST['year'];
$month = $_POST['month'];

foreach ($_POST as $key => $value) {
  $info = '';
  // print 'key:'.$key.' value:'.$value.'<br>';
  if (strstr($key, 'year')) {
    continue;
  }
  if (strstr($key, 'month')) {
    continue;
  }

  if (strstr($key, 'info_'))
    list($info, $info_id, $person_id) = explode('_', $key);
  else
    list($foo, $mod_id, $person_id, $daypart, $day) = explode('_', $key);

  // print "person: $person_id, daypart: $daypart, day: $day<br>";

  if (strlen($info) > 0) {
    $a = array(
      'person_id' => $person_id, 'day' => '0', 'month' => $month, 'year' => $year,
      'daypart' => '0', 'mark' => '', 'info' => $value
    );

    if ($info_id > 0) {
      $a['id'] = $info_id;
      $db->Update($a);
    } else
      $db->Insert($a);
  } elseif (strlen($value) > 0 || $mod_id > 0) {
    $a = array(
      'person_id' => $person_id, 'day' => $day, 'month' => $month, 'year' => $year,
      'daypart' => $daypart, 'mark' => $value
    );
    if ($mod_id > 0) {
      $a['id'] = $mod_id;
      $db->Update($a);
    } else
      $db->Insert($a);
  }
}

$_SESSION['pate_message'] = '<b>Tiedot tallenettu.</b>';

$db->Close();

//print_r( $_POST);
header("Location: index.php?year=$year&month=$month");
