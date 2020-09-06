<?php
require_once 'CDb.php';
require_once 'PateUtils.php';
require_once '../fpdf/fpdf.php';

$db = new CDb;
$db_person = new CDb;
$db->Connect();
$db_person->Connect();

$db->Query('SELECT value FROM settings WHERE name=\'seurakunta\' LIMIT 1');
$srk = $db->GetResultField('value');

$month = $_REQUEST['month'];
$year = $_REQUEST['year'];

class PDF extends FPDF
{
  var $Title;
  var $SkipHeader;
  var $SkipFooter;

  //Load data
  function LoadData($file)
  {
    //Read file lines
    $lines = file($file);
    $data = array();
    foreach ($lines as $line)
      $data[] = explode(';', chop($line));
    return $data;
  }

  //Colored table
  function Table($header, $data, $fontsize = 12, $width = array(45, 75, 62), $height = 6)
  {
    //Colors, line width and bold font
    $this->SetFillColor(255);
    $this->SetTextColor(0);
    $this->SetDrawColor(128, 0, 0);
    $this->SetLineWidth(.2);
    $this->SetFont('Arial', 'B', $fontsize);
    //Header
    $w = $width; //array(60,75,52); // column widths
    for ($i = 0; $i < count($header); $i++)
      $this->Cell($w[$i], $fontsize / 2, $header[$i], 1, 0, 'L', 1);
    $this->Ln();
    //Color and font restoration
    $this->SetFillColor(224, 235, 255);
    $this->SetTextColor(0);
    //$this->SetFont('');
    $this->SetFont('Arial', '', $fontsize);
    //Data
    $fill = 0;
    foreach ($data as $row) {
      $this->Cell($w[0], $height, $row[0], 1, 0, 'L', $fill);
      $this->Cell($w[1], $height, $row[1], 1, 0, 'L', $fill);
      $this->MultiCell($w[2], $height, $row[2], 1, 'L', $fill);
      //$this->Ln();
      $fill = !$fill;
    }
    $this->Cell(array_sum($w), 0, '', 'T');
  }

  //Page header
  function Header()
  {
    if (!$this->SkipHeader) {
      //Logo
      //$this->Image('/var/www/pictures/isokirja_sininen.jpg',10,8,30,8);
      //Arial bold 15
      $this->SetFont('Arial', 'B', 14);
      //Move to the right
      $this->Cell(80);
      //Title
      $this->Cell(30, 7, utf8_decode($this->Title), 0, 0, 'C');
      $this->SetFont('Arial', '', 10);
      $this->Cell(80, 7, 'Tulostettu: ' . Date('j.n.Y G:i'), 0, 0, 'R');
      //Line break
      $this->Ln(5);
    }
  }

  //Page footer
  function Footer()
  {
    global $L, $srk;
    if (!$this->SkipFooter) {
      //Position at 1.5 cm from bottom
      $this->SetY(-15);
      //Arial italic 8
      $this->SetFont('Arial', 'I', 10);
      //Page number

      $this->Line(10, 282, 200, 282);

      $this->Cell(50, 5, 'PaTe 2020 - DataCodex', 0, 0, 'L');
      $this->Cell(90, 5, $srk, 0, 0, 'C');
      $this->Cell(50, 5, 'Sivu ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
  }
}

$db_person->Query('SELECT * FROM persons WHERE visible = 1 ORDER BY firstname');

//      print "kohde_id: $kohde_id<br>";
//print "rows: ".$db->NumRows()."<br>";
//print "ajo: ".$kohderow['ajoohje']."<br>";

$pdf = new PDF();
$pdf->Title = '';
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->Kirjaaja = true;

//Colors, line width and bold font
$pdf->SetFillColor(255);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(.2);

//Header
$pdf->SetFont('Arial', '', 18);
$pdf->Cell(120, 8, utf8_decode(monthofyear($month)));
$pdf->Ln();
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(40, 6, $year);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 6, 'Kirjainkoodit');
$pdf->Ln();

$lineheight = 5;
$width[0] = 40;
$width[1] = 6;
$width[2] = 30;
$width[3] = 6;
$width[4] = 43;
$width[5] = 6;
$width[6] = 52;
$pdf->Cell($width[0]);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[1], $lineheight, 'T', 'LT');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[2], $lineheight, 'toimisto', 'T');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[3], $lineheight, 'M', 'T');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[4], $lineheight, utf8_decode('työmatka'), 'T');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[5], $lineheight, '', 'T');
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell($width[6], $lineheight, utf8_decode('työtehtävät toimip. ulkop.'), 'TR');
$pdf->Ln();

$pdf->Cell($width[0]);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[1], $lineheight, 'P', 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[2], $lineheight, 'palaveri', '');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[3], $lineheight, 'S', '');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[4], $lineheight, 'saarnan-/tekstinvalmistus', '');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[5], $lineheight, 'U1', '');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[6], $lineheight, utf8_decode('koti-/sairaskäynti, toimitukset'), 'R');
$pdf->Ln();

$pdf->Cell($width[0]);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[1], $lineheight, 'K', 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[2], $lineheight, 'kokous', '');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[3], $lineheight, 'A', '');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[4], $lineheight, 'artikkelien valmistus', '');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[5], $lineheight, 'U2', '');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[6], $lineheight, utf8_decode('neuvottelu/edustustehtävät'), 'R');
$pdf->Ln();

$pdf->Cell($width[0]);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[1], $lineheight, 'X', 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[2], $lineheight, utf8_decode('pastoripäivistys'), '');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[3], $lineheight, 'V', '');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[4], $lineheight, utf8_decode('vapaapäivä'), '');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[5], $lineheight, 'U3', '');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[6], $lineheight, 'evankeliointi', 'R');
$pdf->Ln();

$pdf->Cell($width[0]);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[1], $lineheight, 'O', 'LB');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[2], $lineheight, 'opiskelu', 'B');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[3], $lineheight, 'L', 'B');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[4], $lineheight, 'loma', 'B');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($width[5], $lineheight, 'U4', 'B');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($width[6], $lineheight, utf8_decode('työmuoto/pienryhmä/leiri'), 'RB');
$pdf->Ln();

$pdf->Ln();

$daysinmonth = lastdayinmonth($month, $year);

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

$row_title[0] = 'ap.';
$row_title[1] = 'ip.';
$row_title[2] = 'ilta';


$pdf->SetFont('Arial', '', 8);
$pdf->SetLineWidth(.2);
for ($row = 0; $row < 2; $row++) {
  if ($row == 0) {
    $pdf->Cell(20, 4, 'Vapaita', 0, 0, 'L');
    $pdf->Cell(10, 4, $vapaita, 0, 0, 'L');
  } elseif ($row == 1) {
    $pdf->Cell(20, 4, utf8_decode('Työpäiviä'), 0, 0, 'L');
    $pdf->Cell(10, 4, $tyopaivia, 0, 0, 'L');
  }

  $pdf->Cell($width[0] - 30);
  $border = 1;
  for ($d = 1; $d <= $daysinmonth; $d++) {
    $dow = dayofweek($d, $month, $year);
    if ($dow == 0 || $dow == 6 || isholiday($d, $month, $year))
      $pdf->SetLineWidth(.4);
    else
      $pdf->SetLineWidth(.2);

    if ($row == 0)
      $pdf->Cell(5, 5, DOW($dow), $border, 0, 'C');
    if ($row == 1)
      $pdf->Cell(5, 5, $d, $border, 0, 'C');
  }
  $pdf->Ln();
}
$pdf->SetLineWidth(.2);

$pdf->Cell(20, 4, utf8_decode('Työvuoroja'), 0, 0, 'L');
$pdf->Cell(10, 4, $tyovuoroja, 0, 0, 'L');
$pdf->Ln();
$pdf->Ln();


$person_nro = 0;
while (is_array($person_row = $db_person->FetchArray())) {
  $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
  $sql .= 'AND year=' . $year . ' AND month=' . $month . ' AND day!=0 AND mark!=\'\' AND mark!=\'v\' AND mark!=\'V\'';

  $db->Query($sql);
  $merkattuja = $db->NumRows();
  $db->Close();

  $rowheight = 5;

  for ($row = 0; $row < 3; $row++) {
    if ($row == 0)
      $border = 'T';
    elseif ($row == 1)
      $border = '';
    else
      $border = 'B';

    if ($row == 0) {
      $pdf->Cell(26, $rowheight, '', $border . 'LR', 0, 'L');
      $pdf->Cell(8, $rowheight, '', $border . 'LR', 0, 'L');
    } elseif ($row == 1) {
      $pdf->Cell(26, $rowheight, ' ' . utf8_decode($person_row['firstname']), $border . 'LR', 0, 'L');
      $pdf->Cell(8, $rowheight, ($merkattuja - $tyovuoroja), $border . 'LR', 0, 'C');
    } else {
      $pdf->Cell(26, $rowheight, '', $border . 'LR', 0, 'L');
      $pdf->Cell(8, $rowheight, '', $border . 'LR', 0, 'L');
    }

    $pdf->Cell(6, $rowheight, $row_title[$row], 1, 0, 'L');

    for ($d = 1; $d <= $daysinmonth; $d++) {
      $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
      $sql .= 'AND daypart=' . $row . ' AND year=' . $year . ' AND month=' . $month . ' ';
      $sql .= 'AND day=' . $d;

      $db->Query($sql);
      if ($db->NumRows() > 0) {
        $db_row = $db->FetchArray();
        $mod_id = $db_row['id'];
        $value = $db_row['mark'];
      } else {
        $mod_id = 0;
        $value = '';
      }
      $db->Close();

      $dow = dayofweek($d, $month, $year);
      if ($dow == 0 || $dow == 6 || isholiday($d, $month, $year))
        $linewidth = 0.4;
      else
        $linewidth = 0.2;

      $pdf->SetLineWidth($linewidth);

      $tabindex = ($person_nro * $daysinmonth * 3) + ($d * 3) + $row + $person_nro;

      $pdf->Cell(5, $rowheight, strtoupper($value), 1, 0, 'C');
      $pdf->SetLineWidth(.2);
    }
    $pdf->Ln();
  }

  $sql = 'SELECT * FROM modules WHERE person_id=' . $person_row['id'] . ' ';
  $sql .= 'AND daypart=0 AND year=' . $year . ' AND month=' . $month . ' ';
  $sql .= 'AND day=0';

  $db->Query($sql);
  if ($db->NumRows() > 0) {
    $db_row = $db->FetchArray();
    $info_id = $db_row['id'];
    $info = $db_row['info'];
  } else {
    $info = '';
    $info_id = 0;
  }
  $db->Close();

  $pdf->Cell(34, $rowheight, utf8_decode('Lisätiedot:'), 0, 0, 'L');
  $pdf->Cell(5 * $daysinmonth, $rowheight, $info, 0, 0, 'L');

  $pdf->Ln();
  $pdf->Ln();

  $person_nro++;
}

$pdf->Output();
