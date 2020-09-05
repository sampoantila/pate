<?php
require_once 'CDb.php';

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

function dayofweek($day, $month, $year)
{
  $dow = date('w', mktime(0, 0, 0, $month, $day, $year));

  return $dow;
}

function monthofyear($month)
{
  $name = '';

  switch ($month) {
    case 1:
      $name = 'Tammikuu';
      break;
    case 2:
      $name = 'Helmikuu';
      break;
    case 3:
      $name = 'Maaliskuu';
      break;
    case 4:
      $name = 'Huhtikuu';
      break;
    case 5:
      $name = 'Toukokuu';
      break;
    case 6:
      $name = 'Kes&auml;kuu';
      break;
    case 7:
      $name = 'Hein&auml;kuu';
      break;
    case 8:
      $name = 'Elokuu';
      break;
    case 9:
      $name = 'Syyskuu';
      break;
    case 10:
      $name = 'Lokakuu';
      break;
    case 11:
      $name = 'Marraskuu';
      break;
    case 12:
      $name = 'Joulukuu';
      break;
  }
  return $name;
}

function lastdayinmonth($month, $year)
{
  return date("j", mktime(0, 0, 0, $month + 1, 0, $year));
}

function getHolidays($year)
{
  $holidays = getEasterDays($year);

  // Add rest of holidays
  $joulupaiva = new DateTime();
  $joulupaiva->setDate($year, 12, 25);
  $joulupaiva->setTime(0, 0, 0);

  $tapaninpaiva = new DateTime();
  $tapaninpaiva->setDate($year, 12, 26);
  $tapaninpaiva->setTime(0, 0, 0);

  $uusivuosi = new DateTime();
  $uusivuosi->setDate($year, 1, 1);
  $uusivuosi->setTime(0, 0, 0);

  $vappu = new DateTime();
  $vappu->setDate($year, 5, 1);
  $vappu->setTime(0, 0, 0);

  $loppiainen = new DateTime();
  $loppiainen->setDate($year, 1, 6);
  $loppiainen->setTime(0, 0, 0);

  $itsenaisyys = new DateTime();
  $itsenaisyys->setDate($year, 12, 6);
  $itsenaisyys->setTime(0, 0, 0);

  array_push($holidays, $uusivuosi, $loppiainen, $vappu, $itsenaisyys, $joulupaiva, $tapaninpaiva);

  return $holidays;
}

function getEasterDays($year)
{
  $a = $year % 19;
  $b = $year % 4;
  $c = $year % 7;
  $d = (19 * $a + 24) % 30;
  $e = (2 * $b + 4 * $c + 6 * $d + 5) % 7;

  $day = 22 + $d + $e;

  $paasiainen = new DateTime();
  $paasiainen->setTime(0, 0, 0);

  if ($day <= 31) {
    $paasiainen->setDate($year, 3, $day);
  } else {
    $day = $d + $e - 9;
    $paasiainen->setDate($year, 4, $day);
    $weekInterval = new DateInterval('P1W');

    if ($day == 26) {
      $paasiainen->sub($weekInterval);
    }
    if ($day == 25 && $d == 28 && $a > 10) {
      $paasiainen->sub($weekInterval);
    }
  }

  $helatorstai = clone $paasiainen;
  $helatorstai->add(new DateInterval('P39D'));

  $pitkaperjantai = clone $paasiainen;
  $pitkaperjantai->sub(new DateInterval('P2D'));

  $toinenpaasiainen = clone $paasiainen;
  $toinenpaasiainen->add(new DateInterval('P1D'));

  return array($pitkaperjantai, $paasiainen, $toinenpaasiainen, $helatorstai);
}

function isholiday($day, $month, $year)
{
  $retval = false;

  $holidays = getHolidays($year);

  $compare = new DateTime();
  $compare->setDate($year, $month, $day);
  $compare->setTime(0, 0, 0);

  foreach ($holidays as $date) {
    if ($date == $compare) {
      $retval = true;
      break;
    }
  }

  return $retval;
}
