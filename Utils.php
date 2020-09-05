<?php

function set_form_values($arr)
{
  $_SESSION['form_values'] = $arr;
}

function get_form_values()
{
  return $_SESSION['form_values'];
}

function set_form_values2($arr)
{
  $_SESSION['form_values2'] = $arr;
}

function get_form_values2()
{
  return $_SESSION['form_values2'];
}

function set_form_values3($arr)
{
  $_SESSION['form_values3'] = $arr;
}

function get_form_values3()
{
  return $_SESSION['form_values3'];
}

function V($arg, $print = true)
{
  $a = get_form_values();
  if ($print == true)
    print $a[$arg];
  else
    return $a[$arg];
}

function VC($arg)
{
  $a = get_form_values();

  if ($a[$arg] == 1)
    print 'checked';
}

function V2($arg, $print = true)
{
  $a = get_form_values2();
  if ($print == true)
    print $a[$arg];
  else
    return $a[$arg];
}

function VC2($arg)
{
  $a = get_form_values2();

  if ($a[$arg] == 1)
    print 'checked';
}

function V3($arg, $print = true)
{
  $a = get_form_values3();
  if ($print == true)
    print $a[$arg];
  else
    return $a[$arg];
}

function VC3($arg)
{
  $a = get_form_values3();

  if ($a[$arg] == 1)
    print 'checked';
}

function MakeCB($field, $options = '', $valueset = 1)
{
  print '<input type="hidden" name="fc_' . $field . '" value="0">';
  print "\n";

  print '<input type="checkbox" id="' . $field . '" name="fc_' . $field . '" ';
  if ($valueset == 2)
    VC2($field);
  elseif ($valueset == 3)
    VC3($field);
  else
    VC($field);
  print "  onchange=\"m()\"  $options >\n";
}

function parse_fields($print_values = false)
{
  $ret = array();

  foreach ($_POST as $name => $value) {
    $vals = explode('f_', $name);
    if ($vals[1] == '') {
      $vals = explode('fc_', $name);
      if ($print_values)
        print 'name: ' . $vals[1] . " value : $value<br>";
      if ($value == 'on')
        $value = '1';
      else
        $value = '0';
    }
    if ($print_values)
      print 'xxx-field: [' . $vals[1] . "] = $value<br>";
    if ($vals[1] != '')
      $ret[$vals[1]] = $value;
  }

  return $ret;
}

function DOW($d) // Day Of Week
{
  switch ($d) {
    case 0:
      $fi_day = 'Su';
      break;
    case 1:
      $fi_day = 'Ma';
      break;
    case 2:
      $fi_day = 'Ti';
      break;
    case 3:
      $fi_day = 'Ke';
      break;
    case 4:
      $fi_day = 'To';
      break;
    case 5:
      $fi_day = 'Pe';
      break;
    case 6:
      $fi_day = 'La';
      break;
    default:
      $fi_day = '??';
      break;
  }

  return $fi_day;
}

function GetMonthName($month)
{
  switch ($month) {
    case 1:
      print 'Tammikuu';
      break;
    case 2:
      print 'Helmikuu';
      break;
    case 3:
      print 'Maaliskuu';
      break;
    case 4:
      print 'Huhtikuu';
      break;
    case 5:
      print 'Toukokuu';
      break;
    case 6:
      print 'Kes&auml;kuu';
      break;
    case 7:
      print 'Hein&auml;kuu';
      break;
    case 8:
      print 'Elokuu';
      break;
    case 9:
      print 'Syyskuu';
      break;
    case 10:
      print 'Lokakuu';
      break;
    case 11:
      print 'Marraskuu';
      break;
    case 12:
      print 'Joulukuu';
      break;
  }
}

function IsDay($pv, $day, $print = true)
{
  $retval = '';
  $retbool = false;

  if ($day == 'la' || $day == 'su') {
    $day0 = $day . '0';
    $la1 = strstr($pv, $day0);
    if (substr($la1, 0, 3) != $day0) {
      // la0 paivaa ei ole, joten vertaa suoraan
      $retval = (strstr($pv, $day) ? ' checked ' : '');
      $retbool = (strstr($pv, $day) ? true : false);
    } else {
      $pv2 = substr($la1, 3, strlen($pv));
      $la2 = strstr($pv2, $day);
      if (substr($la2, 0, 2) == $day) {
        $retval = ' checked ';
        $retbool = true;
      }
    }
  } elseif ($day == 'ma') {
    $day1 = $day . '1';
    $ma1 = strstr($pv, $day);
    if (substr($ma1, 0, 3) != $day1) {
      // ma1 paivaa ei ole, joten vertaa suoraan
      $retval = (strstr($pv, $day) ? ' checked ' : '');
      $retbool = (strstr($pv, $day) ? true : false);
    } else {
      $pv2 = substr($ma1, 3, strlen($pv));
      $ma2 = strstr($pv2, $day);
      if (substr($ma2, 0, 2) == $day) {
        $retval = ' checked ';
        $retbool = true;
      }
    }
  } else {
    $retval = ((strstr($pv, $day) ? ' checked ' : ''));
    $retbool = ((strstr($pv, $day) ? true : false));
  }

  if ($print)
    print $retval;

  return $retbool;
}


function IsMSIE()
{
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') != 0)
    return true;
  else
    return false;
}

// MISC
function GetNeedMin($day, $tyopiste_id)
{
  global $db;
  $not_set = false;

  $maara = GetDaySum($day, $tyopiste_id, -1); // lasketaan ylipaataan ilmoittautuneista, ei saapuneista

  $sql = "SELECT * FROM tyopisteet WHERE id=$tyopiste_id";

  $db->Query($sql);

  $row = $db->FetchArray();

  //print "maara: $maara";
  //print "min: " . $row[$day . '_min'];

  if ($row[$day . '_min'] == 0)
    $not_set = true;

  $need = $row[$day . '_min'] - $maara;
  $need_max = $row[$day . '_max'] - $maara;

  //print "la0_min: " . $need;

  // format value
  if ($not_set)
    $need = '<font color="#3355cc">' . -$need . '</font>';
  else if ($need_max < 0)
    $need = '<font color="#ff8c00"><b>' . $need_max . '</b></font>';
  else if ($need < 0)
    $need = '<font color="#00e0e0">' . $need . '</font>';
  else if ($need > 0 && $need < 5)
    $need = $need;
  else if ($need > 5 && $need < 10)
    $need = '<b>' . $need . '</b>';
  else if ($need > 10)
    $need = '<font color="#ff2244"><b>' . $need . '</b></font>';

  return $need;
}

function GetNeedMax($day, $tyopiste_id)
{
  global $db;

  $maara = GetDaySum($day, $tyopiste_id, -1); // lasketaan ylipaataan ilmoittautuneista, ei saapuneista

  $sql = "SELECT * FROM tyopisteet WHERE id=$tyopiste_id";
  $db->Query($sql);

  $row = $db->FetchArray();

  //print "maara: $maara";
  //print "min: " . $row[$day . '_min'];

  $need = $row[$day . '_max'] - $maara;

  //print "la0_min: " . $need;
  return ($need > 0 ? $need : 0);
}


function GetDaySum($aDay, $aTyopisteId = 0, $aIlm = -1)
{
  global $db, $S;
  $aVuosi = $S->Year();

  if ($aDay == 'la0') $bit = 1;
  else if ($aDay == 'su0') $bit = 2;
  else if ($aDay == 'ma') $bit = 4;
  else if ($aDay == 'ti') $bit = 8;
  else if ($aDay == 'ke') $bit = 16;
  else if ($aDay == 'to') $bit = 32;
  else if ($aDay == 'pe') $bit = 64;
  else if ($aDay == 'la') $bit = 128;
  else if ($aDay == 'su') $bit = 256;
  else $bit = 0;

  $sql = 'SELECT COUNT(id) as maara FROM tyot ';
  $sql .= "WHERE vuosi=$aVuosi ";

  if ($bit != 0) $sql .= " AND tyot.paivat&$bit ";
  if ($aIlm != -1) $sql .= ' AND tyot.ilmoittautunut=' . ($aIlm == 1 ? '1' : '0') . ' ';
  if ($aTyopisteId != 0) $sql .= " AND tyot.tyopiste_id=$aTyopisteId ";

  $db->Query($sql);

  $row = $db->FetchArray();

  return $row['maara'];
}

function MakeDate($timestamp)
{
  if ($timestamp == "" || $timestamp == "00000000000000")
    return "";

  // format time stamp to clean date time
  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT DATE_FORMAT('$timestamp','%e.%c.%y klo %k:%i') as time");

  $time = $db->GetResultField('time');

  $db->Close();
  return $time;
}

function IsDayChecked($record, $day)
{
  print((strstr($record['paivat'], $day) ? 'checked' : ''));
}

function MakeMuuttaja($person_id, $date, $show_group = false)
{
  print '<span class="muuttaja">';
  $mid = $person_id;
  if ($mid != '') {
    $mnimi = GetPersonNameById($mid, false, true);
    $mpvm = MakeDate($date);

    if ($show_group) {
      $mryhma = GetPersonGroupNameById($mid);
      print "$mnimi ($mryhma), $mpvm";
    } else
      print "($mnimi, $mpvm)";
  } else {
    $mpvm = MakeDate($date);
    print $mpvm;
  }
  print '</span>';
}

function addtoset($set, $value, $clear = false)
{
  static $set;

  if ($clear)
    $set = '';
  else {
    if ($set != '' && $value != '')
      $set .= ",$value";
    else if ($value != '')
      $set .= $value;
  }

  return $set;
}

function clearset()
{
  addtoset('', '', true);
}

function makedayset($values, $prefix = '')
{
  $set = '';
  foreach ($values as $key => $value) {
    //print "key: $key, value: $value<br>";
    switch ($key) {
      case $prefix . 'la0':
        if ($value == '1') addtoset($set, 'la0');
        break;
      case $prefix . 'su0':
        if ($value == '1') addtoset($set, 'su0');
        break;
      case $prefix . 'ma':
        if ($value == '1') addtoset($set, 'ma');
        break;
      case $prefix . 'ti':
        if ($value == '1') addtoset($set, 'ti');
        break;
      case $prefix . 'ke':
        if ($value == '1') addtoset($set, 'ke');
        break;
      case $prefix . 'to':
        if ($value == '1') addtoset($set, 'to');
        break;
      case $prefix . 'pe':
        if ($value == '1') addtoset($set, 'pe');
        break;
      case $prefix . 'la':
        if ($value == '1') addtoset($set, 'la');
        break;
      case $prefix . 'su':
        if ($value == '1') addtoset($set, 'su');
        break;
      case $prefix . 'ma1':
        if ($value == '1') addtoset($set, 'ma1');
        break;
    }
  }

  return addtoset('', '');
}

function removedays($values, $prefix = '')
{
  unset($values[$prefix . 'la0']);
  unset($values[$prefix . 'su0']);
  unset($values[$prefix . 'ma']);
  unset($values[$prefix . 'ti']);
  unset($values[$prefix . 'ke']);
  unset($values[$prefix . 'to']);
  unset($values[$prefix . 'pe']);
  unset($values[$prefix . 'la']);
  unset($values[$prefix . 'su']);
  unset($values[$prefix . 'ma1']);
  return $values;
}

function HasAccessWork($workid, $userid, $print = false)
{
  global $S;

  $db = new CDb;
  $db->Connect();

  $retval = false;

  if ($print) {
    print "workid: $workid, userid: $userid<br>";
    print "Tyopiste: " . GetWorkUnitById($workid) . "<br>";
    print "henkilon tyopiste: " . GetWorkUnitByPersonId($userid, true) . "<br>";
    print "valittutyopiste: " . $S->SelectionTP();
  }
  $sql = "SELECT id,asema FROM tyot WHERE tyopiste_id='$workid' AND henkilo_id='$userid' AND vuosi='" . $S->Year() . "' LIMIT 1";
  $db->Query($sql);
  if ($print) {
    print "sql: $sql<br>";
    print "riveja:" . $db->NumRows() . "<br>";
  }
  if ($db->NumRows() > 0) {
    if ($db->GetResultField('asema') == 'esimies' || $db->GetResultField('asema') == 'vuorovastaava') {
      $retval = true;
      if ($print)
        print "asema: " . $db->GetResultField('asema') . "<br>";
    }
  }
  $db->Close();
  return $retval;
}

function finecase($str, $type = 'words')
{
  $arr = explode(' ', $str);
  foreach ($arr as $key => $value) {
    if (preg_match("^[AEIOU���]{1,2}$", $value)) {
      $value = strtolower($value);
    }
    if (preg_match("^[B-DF-HJ-NP-TV-Z][AEIOU������]$", $value)) {
      $value = strtolower($value);
    }
    if (preg_match("^[AEIOU���][B-DF-HJ-NP-TV-Z]$", $value)) {
      $value = strtolower($value);
    }
    if (preg_match("^[A-Z���-]{3,50}$", $value)) {
      if ($type == "words" or !$key) {
        $value = ucfirst(strtolower($value));
      } else {
        $value = strtolower($value);
      }
    } elseif (preg_match("^.{3,50}$", $value)) {
      if ($type == "words" or !$key) {
        $value = ucfirst($value);
      }
    }
    if (preg_match("^[A-Z���]+([!-/:-@\-`{-~]+[A-Z���]+)+$", $value)) {
      if ($type == "words" or !$key) {
        $value = ucwords(strtolower($value));
      } else {
        $value = strtolower($value);
      }
    }
    if (preg_match("^[A-Z���]{3,50}(,|\.+)$", $value)) {
      if ($type == "words" or !$key) {
        $value = ucfirst(strtolower($value));
      } else {
        $value = strtolower($value);
      }
    }
    if (preg_match("^[b-df-hj-np-tv-z���]+$", $value)) {
      $value = strtoupper($value);
    }
    if (preg_match("^.+$", $value) and preg_match("^.*(\.+|\?|!|;)$", $arr[$key - 1])) {
      $value = ucfirst($value);
    }

    $arr[$key] = $value;
  }
  $str = join(' ', $arr);
  return $str;
}

function t_header()
{
  return ('Location: ' . 'http' . ':');
}

function mylower($string)
{
  $ret = strtolower($string);
  $ret = str_replace('�', '�', $ret);
  $ret = str_replace('�', '�', $ret);
  $ret = str_replace('�', '�', $ret);

  return $ret;
}

function myscandstoasc($string)
{
  $ret = str_replace('�', 'a', $string);
  $ret = str_replace('�', 'A', $ret);
  $ret = str_replace('�', 'a', $ret);
  $ret = str_replace('�', 'A', $ret);
  $ret = str_replace('�', 'o', $ret);
  $ret = str_replace('�', 'O', $ret);

  return $ret;
}

// attach_filepath on array, jossa on listana liitettavat tiedostot
function xmail($email_address, $email_cc, $email_bcc, $email_from, $subject, $msg, $attach_filepath)
{
  $b = 0;
  $mail_attached = '';
  $boundary = '000XMAIL000';
  if (count($attach_filepath) > 0) {
    for ($a = 0; $a < count($attach_filepath); $a++) {
      if ($fp = fopen($attach_filepath[$a], 'rb')) {
        $file_name = basename($attach_filepath[$a]);
        $content[$b] = fread($fp, filesize($attach_filepath[$a]));
        $mail_attached .= '--' . $boundary . "\n"
          . "Content-Type: image/jpeg; name=\"$file_name\"\n"
          . "Content-Transfer-Encoding: base64\n"
          . "Content-Disposition: inline; filename=\"$file_name\"\n\n"
          . chunk_split(base64_encode($content[$b])) . "\n";
        $b++;
        fclose($fp);
      } else {
        echo 'Tiedoston avaaminen ep�onnistui. File: ' . $attach_filepath[$a];
      }
    }
    $mail_attached .= '--' . $boundary . "\n";
    $add_header = "MIME-Version: 1.0\n"
      . "Content-Type: multipart/mixed; boundary=\"$boundary\"; Message-ID: <" . md5($email_from) . "@tarmo.homelinux.net>";
    $mail_content = "--" . $boundary . "\n"
      . "Content-Type: text/plain; charset=\"iso-8859-1\"\n"
      . "Content-Transfer-Encoding: 8bit\n\n"
      . $msg . "\n\n" . $mail_attached;
    return mail(
      $email_address,
      $subject,
      $mail_content,
      "From: " . $email_from . "\nCC: " . $email_cc . "\nBCC: " . $email_bcc
        . "\nErrors-To: " . $email_from . "\n" . $add_header
    );
  } else {
    return mail(
      $email_address,
      $subject,
      $msg,
      "From: " . $email_from . "\nCC: " . $email_cc . "\nBCC: " . $email_bcc
        . "\nErrors-To: " . $email_from
    );
  }
}

function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function make_random($min, $max)
{
  mt_srand(make_seed());
  $randval = mt_rand($min, $max);

  return $randval;
}
