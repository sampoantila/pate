<?php
session_start();

require_once 'CDb.php';
require_once 'SelectUtils.php';
require_once 'PateUtils.php';
?>

<!-- (c) Copyright 2006 - 2020 DataCodex -->

<!DOCTYPE html>

<html lang="fi">

<head>
  <meta http-equiv="content-type" content="text/html;charset=utf-8">
  <script src="form.js"></script>
  <script src="pate.js"></script>
  <link rel="stylesheet" href="pate.css" type="text/css">
  <title>PaTe - Pastorien teot</title>
</head>

<body onload="modified(false)">
  <?php
  if (isset($_REQUEST['month']))
    $month = $_REQUEST['month'];

  if (isset($_REQUEST['year']))
    $year = $_REQUEST['year'];

  if (!isset($month) || $month == 0)
    $month = date('m');

  if (!isset($year) || $year == 0)
    $year = date('Y');

  // print "month: $month<br>year=$year<br>";

  $daysinmonth = lastdayinmonth($month, $year);

  $db = new CDb;
  $db->Connect();
  $db->Query('SELECT * FROM persons WHERE visible = 1 ORDER BY firstname');

  $db_mod = new CDb;
  $db_mod->Connect();

  $person_count = $db->NumRows();

  $vapaita = 0;
  $tyopaivia = 0;
  $tyovuoroja = 0;
  for ($i = 1; $i <= $daysinmonth; $i++) {
    $dw = dayofweek($i, $month, $year);
    if ($dw == 0 || $dw == 6 || isholiday($i, $month, $year))
      $vapaita++;
    else
      $tyopaivia++;
  }
  $tyovuoroja = $tyopaivia * 2;
  ?>

  <table class="rajaus">
    <tr>
      <td class="menu">
        VALIKKO
        <br>
        <br>
        Valitse&nbsp;vuosi:<br>
        <?php
        $yearCombo = array(date('Y') - 3, date('Y') - 2, date('Y') - 1, date('Y'), date('Y') + 1);
        MakeSelect($yearCombo, 'year', $year, 'class="menu" id="year" onchange="call(\'?year=\'+getElementById(\'year\').value+\'&month=\'+getElementById(\'month\').value)"'); ?>
        <br>
        <br>
        Valitse&nbsp;kuukausi:<br>
        <?php MakeMonthSelect('month', $month, 'class="menu" id="month" onchange="call(\'?year=\'+getElementById(\'year\').value+\'&month=\'+getElementById(\'month\').value)"'); ?>
        <br>
        <br>
        <button class="menu" onclick="call('report.php?year='+getElementById('year').value+'&month='+getElementById('month').value)">Tulosta kk</button>
        <br>
        <br>
        &nbsp;&nbsp;<?php if (isset($_SESSION['pate_message'])) {
                      print $_SESSION['pate_message'];
                      unset($_SESSION['pate_message']);
                    } ?><br>
        &nbsp;&nbsp;<button style="width:110px; height:25px;" onclick="getElementById('modules').submit()">Tallenna tiedot</button><br><br>

      </td>
      <td class="top">

        <table>
          <tr>
            <td>
              <table style="width:100%">
                <tr>
                  <td style="font-size:15px; font-weight:bold;">
                    <b><?php print htmlspecialchars(monthofyear($month)); ?><br><span style="font-size:12px;"><?php print $year; ?></span>&nbsp;&nbsp;&nbsp;</b>
                  </td>
                  <td></td>
                </tr>
                <tr>
                  <td style="vertical-align: bottom;">
                    <table>
                      <tr>
                        <td style="color: #00aa22; font-weight:bold;">
                          Vapaita</td>
                        <td style="color: #00aa22; font-weight:bold;">
                          <?php print $vapaita; ?>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          Ty&ouml;p&auml;ivi&auml;
                        </td>
                        <td>
                          <?php print $tyopaivia; ?>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          Ty&ouml;vuoroja&nbsp;
                        </td>
                        <td>
                          <?php print $tyovuoroja; ?>
                        </td>
                      </tr>
                    </table><br>
                  </td>
                  <td>
                    <?php include "legend.php" ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <form method="post" action="store.php" id="modules">
                <input type="hidden" name="year" value="<?php print $year; ?>">
                <input type="hidden" name="month" value="<?php print $month; ?>">
                <?php

                $row_title[0] = 'ap.';
                $row_title[1] = 'ip.';
                $row_title[2] = 'ilta';

                print '<table class="person">';
                for ($row = 0; $row < 2; $row++) {
                  print '<tr>';
                  if ($row == 0) {
                    print '<td class="mod_hidden_name" rowspan="2">&nbsp;</td>' . "\n";
                    print '<td class="mod_hidden_count" rowspan="2">&nbsp;</td>';
                  }
                  print '<td class="mod_hidden_daypart">&nbsp;</td>' . "\n";
                  for ($d = 1; $d <= $daysinmonth; $d++) {
                    $dow = dayofweek($d, $month, $year);
                    if ($dow == 0 || $dow == 6 || isholiday($d, $month, $year))
                      $class = 'mod_weekend';
                    else
                      $class = 'mod_day_title';

                    if ($row == 0)
                      print '<td class="' . $class . '">' . DOW($dow) . '</td>';
                    if ($row == 1)
                      print '<td class="' . $class . '">' . $d . '</td>';
                  }
                  print '</tr>' . "\n";
                }
                //print '</table>';
                //print '<table><tr class="spacer"><td></td></tr></table>'."\n";
                print '<tr class="spacer"><td colspan="' . ($daysinmonth + 3) . '"></td></tr>' . "\n";

                $person_nro = 0;
                while (is_array($person_row = $db->FetchArray())) {
                  // Hae persoonan moduulit tietokannasta, filterina kuluva kuukausi ja vuosi

                  // Hae vapaat
                  $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
                  $sql .= 'AND year=' . $year . ' AND month=' . $month . ' AND (mark=\'v\' OR mark=\'V\')';

                  $db_mod->Query($sql);
                  $merkattuja_vapaita = $db_mod->NumRows();
                  $db_mod->Close();

                  // tee siita taulukko $module[paiva][paivaosa]

                  $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
                  $sql .= 'AND year=' . $year . ' AND month=' . $month . ' AND day!=0 AND mark!=\'\' AND mark!=\'v\' AND mark!=\'V\'';

                  $db_mod->Query($sql);
                  $merkattuja = $db_mod->NumRows();
                  $db_mod->Close();

                  $vapaat = ($merkattuja_vapaita - $vapaita);
                  $tyovuorot = ($merkattuja - $tyovuoroja);

                  if ($vapaat > 0) $vapaat = '+' . $vapaat;
                  if ($tyovuorot > 0) $tyovuorot = '+' . $tyovuorot;

                  //  print '<table class="person">';
                  for ($row = 0; $row < 3; $row++) {
                    print '<tr>';
                    if ($row == 0) {
                      print '<td class="mod_name" rowspan="3">&nbsp;' . $person_row['firstname'] . '</td>' . "\n";
                      print '<td class="mod_count" rowspan="3">';
                      print '<input style="color: #00aa22; font-weight:bold;" class="mod_counter" id="freecounter_' . $person_row['id'] . '" type="text" value="' . $vapaat . '" readonly>' . "\n";
                      print '<input class="mod_counter" id="counter_' . $person_row['id'] . '" type="text" value="' . $tyovuorot . '" readonly></td>' . "\n";
                    }
                    print '<td class="mod_daypart">' . $row_title[$row] . '</td>' . "\n";
                    for ($d = 1; $d <= $daysinmonth; $d++) {
                      $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
                      $sql .= 'AND daypart=' . $row . ' AND year=' . $year . ' AND month=' . $month . ' ';
                      $sql .= 'AND day=' . $d;

                      $db_mod->Query($sql);
                      if ($db_mod->NumRows() > 0) {
                        $db_mod_row = $db_mod->FetchArray();
                        $mod_id = $db_mod_row['id'];
                        $value = $db_mod_row['mark'];
                      } else {
                        $mod_id = 0;
                        $value = '';
                      }
                      $db_mod->Close();

                      $dow = dayofweek($d, $month, $year);
                      if ($dow == 0 || $dow == 6 || isholiday($d, $month, $year))
                        $class = 'mod_weekend';
                      else
                        $class = 'mod_day';

                      $tabindex = ($person_nro * $daysinmonth * 3) + ($d * 3) + $row + $person_nro;
                      print '<td class="mod_day"><input type="text" maxlength="2" class="' . $class . '_input" tabindex="' . $tabindex . '" ';
                      print 'name="mod_' . $mod_id . '_' . $person_row['id'] . '_' . $row . '_' . $d . '" value="' . $value . '" ';
                      print 'id="mod_' . $person_row['id'] . '_' . $row . '_' . $d . '" ';
                      print 'onchange="countmods(' . $person_row['id'] . ',' . $daysinmonth . ',' . $tyovuoroja . ',' . $vapaita . ');"></td>';
                    }
                    print '</tr>' . "\n";
                  }

                  print '<tr>';

                  $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
                  $sql .= 'AND daypart=0 AND year=' . $year . ' AND month=' . $month . ' ';
                  $sql .= 'AND day=0';

                  $db_mod->Query($sql);
                  if ($db_mod->NumRows() > 0) {
                    $db_mod_row = $db_mod->FetchArray();
                    $info_id = $db_mod_row['id'];
                    $info = $db_mod_row['info'];
                  } else {
                    $info = '';
                    $info_id = 0;
                  }
                  $db_mod->Close();

                  print '<td colspan="3"><span style="vertical-align: top;">Lis&auml;tiedot:&nbsp;</span></td><td colspan="' . $daysinmonth . '"><textarea cols=50 rows=1 ';
                  print 'tabindex="' . $tabindex++ . '" style="width: 600px;" name="info_' . $info_id . '_' . $person_row['id'] . '">' . $info . '</textarea></td>';
                  print '</tr>' . "\n";

                  //  print '</table>';
                  //  print '<table><tr class="spacer"><td></td></tr></table>'."\n";
                  print '<tr class="spacer"><td colspan="' . ($daysinmonth + 3) . '"></td></tr>' . "\n";
                  $person_nro++;
                }

                print '</table>' . "\n";
                $db->Close();
                ?>
              </form>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <hr>
</body>

</html>