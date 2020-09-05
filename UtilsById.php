<?php
require_once 'Utils.php';

function GetRoomUsage($id)
{
  global $S;
  $db = new CDb;
  $db->Connect();
  $retval = '';

  $sql = 'SELECT count(majoitushuoneet.id) AS maara, majoitushuoneet.nimi, majoitus.id AS majoitus, majoitus.taynna AS taynna, majoitushuoneet.*, ';
  $sql .= 'sum(pe0) AS pe0,sum(la0) AS la0,sum(su0) AS su0,sum(ma1) AS ma1,sum(ti1) AS ti1,sum(ke1) AS ke1,sum(to1) AS to1,sum(pe1) AS pe1,sum(la1) AS la1,sum(su1) AS su1,sum(ma2) AS ma2 ';
  $sql .= 'FROM majoitus ';
  $sql .= 'LEFT JOIN majoitushuoneet ON majoitus.majoitushuone_id=majoitushuoneet.id ';
  $sql .= 'LEFT JOIN majoituskohteet ON majoitushuoneet.majoituskohde_id=majoituskohteet.id ';
  $sql .= 'WHERE majoitushuoneet.id=' . $id . ' AND vuosi=' . $S->Year() . ' ';
  $sql .= 'GROUP BY majoitushuoneet.id ';

  if ($id > 0) {
    $db->Query($sql);

    if ($db->NumRows() > 0) {
      $row = $db->FetchArray();
      $db->Close();
      return $row;
    } else {
      $retval = array('maara' => 0);
    }
  } else {
    $retval = 'id is null';
  }

  $db->Close();
  return $retval;
}

function GetPersonsInRoom($id)
{
  global $S;
  $db = new CDb;
  $db->Connect();
  $retval = '';

  if ($id > 0) {
    $db->Query('SELECT * FROM majoitus LEFT JOIN henkilot ON henkilo_id=henkilot.id WHERE majoitushuone_id=' . $id . ' AND vuosi=' . $S->Year());

    if ($db->NumRows() > 0) {
      while (is_array($row = $db->FetchArray())) {
        $retval .= $row['sukunimi'] . ' ' . $row['etunimi'] . ', ' . $row['kaupunki'] . ',';
        if (strlen($row['lapset']) > 0)
          $retval .= ' (' . $row['lapset'] . ')';
        $retval .= "\n" . '  - p&auml;iv&auml;t: ' . ($row['pe0'] ? 'pe' : '');
        $retval .= ' ' . ($row['la0'] ? 'la' : '');
        $retval .= ' ' . ($row['su0'] ? 'su' : '');
        $retval .= ' ' . ($row['ma1'] ? 'ma' : '');
        $retval .= ' ' . ($row['ti1'] ? 'ti' : '');
        $retval .= ' ' . ($row['ke1'] ? 'ke' : '');
        $retval .= ' ' . ($row['to1'] ? 'to' : '');
        $retval .= ' ' . ($row['pe1'] ? 'pe' : '');
        $retval .= ' ' . ($row['la1'] ? 'la' : '');
        $retval .= ' ' . ($row['su1'] ? 'su' : '');
        $retval .= ' ' . ($row['ma2'] ? 'ma' : '');
        $retval .= "\n";
      }
    } else {
      $retval .= '[ei majoitettuja]';
    }
  } else {
    $retval .= '[ei majoitettuja]';
  }

  return $retval;
}

function GetChurchIdByContactPersonId($person_id)
{
  $retval = 0;

  if ($person_id > 0) {
    $db = new CDb;
    $db->Connect();
    $db->Query('SELECT id FROM seurakunnat WHERE yhteyshlo_id=' . $person_id);
    if ($db->NumRows() > 0)
      $retval = $db->GetResultField('id');
    $db->Close();
  }

  return $retval;
}

function GetHistoryByPersonId($person_id, $workunit_id, $year)
{
  $retval = false;

  if ($person_id > 0 && $workunit_id > 0) {
    $db = new CDb;
    $db->Connect();
    $db->Query('SELECT * FROM tyot WHERE henkilo_id=' . $person_id . ' AND tyopiste_id=' . $workunit_id . ' AND vuosi=' . $year);
    if ($db->NumRows() > 0)
      $retval = true;
    $db->Close();
  }

  return $retval;
}

function GetRoleByPersonId($id)
{
  global $S;
  $retval = '';

  if ($id == 0 || $id == '')
    return $retval;

  $db = new CDb;
  $db->Connect();
  $db->Query('SELECT asema FROM tyot WHERE henkilo_id=' . $id . ' AND vuosi=' . $S->Year() . ' LIMIT 1');
  if ($db->NumRows() > 0)
    $retval = $db->GetResultField('asema');
  $db->Close();

  return $retval;
}

function GetMajoitusToive($id, $lapset = false, $lisatieto = false)
{
  if ($id == 0 || $id == '')
    return '';

  global $S;
  $db = new CDb;
  $db->Connect();
  $db->Query('SELECT majoitustoive_teksti, lapset, lisatietoja FROM majoitus WHERE id=' . $id . ' AND vuosi=' . $S->Year());

  $retval = '';
  if ($db->NumRows() > 0) {
    if ($lapset)
      $retval = $db->GetResultField('lapset');
    elseif ($lisatieto)
      $retval = $db->GetResultField('lisatietoja');
    else
      $retval = $db->GetResultField('majoitustoive_teksti');
  }

  $db->Close();

  return $retval;
}

function GetPersonsByRoomId($id)
{
  if ($id == 0 || $id == '')
    return '';

  global $S;
  $db = new CDb;
  $db->Connect();
  $db->Query('SELECT * FROM majoitus LEFT JOIN henkilot ON henkilo_id=henkilot.id WHERE majoitushuone_id=' . $id . ' AND majoitus.vuosi=' . $S->Year());

  $persons = '';
  $kids = '';
  if ($db->NumRows() > 0) {
    while (is_array($row = $db->FetchArray())) {
      $persons .= $row['sukunimi'] . ' ' . $row['etunimi'] . ', ';
      $kids .= $row['lapset'];
    }
    $persons .= $kids;
  } else {
    $persons .= '[Ei majoitettuja]';
  }

  $db->Close();

  return $persons;
  //return '1234567890123456789012345678901234567890123456789012345678901234567890'; 
}

function GetWorkUnitByPersonId($id, $print = false)
{
  if ($id == 0 || $id == '')
    return '';

  global $S;

  $db = new CDb;
  $db->Connect();
  $sql = 'SELECT tyopiste_id FROM tyot WHERE henkilo_id=' . $id . ' AND vuosi=' . $S->Year();
  $db->Query($sql);
  if ($print) {
    print "sql: $sql<br>";
    print "riveja: " . $db->NumRows() . "<br>";
  }
  $work_id = 0;

  if ($db->NumRows() > 0)
    $work_id = $db->GetResultField('tyopiste_id');

  $db->Close();

  return $work_id;
}

function GetSectionName($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT name FROM syssection WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('name');
  else
    $nimi = "";

  $db->Close();

  return $nimi;
}

function GetSectionId($name)
{
  if ($name == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT id FROM syssection WHERE name='" . $name . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $id = $db->GetResultField('id');
  else
    $id = "";

  $db->Close();

  return $id;
}

function GetTabName($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT name FROM systab WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('name');
  else
    $nimi = "";

  $db->Close();

  return $nimi;
}

function GetTabId($name, $section_id = 0)
{
  if ($name == "")
    return "";

  $db = new CDb;
  $db->Connect();
  if ($section_id > 0)
    $sql = "SELECT id FROM systab WHERE name='" . $name . "' AND section_id=" . $section_id . " LIMIT 1";
  else
    $sql = "SELECT id FROM systab WHERE name='" . $name . "' LIMIT 1";

  $db->Query($sql);

  if ($db->NumRows() > 0)
    $id = $db->GetResultField('id');
  else
    $id = "";

  $db->Close();

  return $id;
}

function GetSuosittelijaByTyoId($id, $fullinfo = false)
{
  if ($id == 0 || $id == '')
    return ' v��r� id ';

  $retval = '';
  $db = new CDb;
  $db->Connect();
  $db->Query('SELECT * FROM tyot LEFT JOIN seurakunnat ON seurakunnat.id=tyot.suositus_srk_id LEFT JOIN henkilot ON seurakunnat.yhteyshlo_id=henkilot.id WHERE tyot.id=' . $id . ' LIMIT 1');

  if ($db->NumRows() > 0) {
    $row = $db->FetchArray();
    if ($row['suositus_srk_id'] > 0) {
      $retval .= $row['nimi'] . '/';
      $retval .= GetFullNameById($row['yhteyshlo_id']);
      if ($fullinfo) {
        $retval .= "\n" . 'Email: ' . GetEmailById($row['yhteyshlo_id']) . "\n";
        $retval .= 'Puhelin: ' . GetPhoneById($row['yhteyshlo_id']) . "\n";
        $retval .= 'Tarkemmat yhteystiedot l�ytyy Tarmosta.' . "\n";
      }
    } else {
      $retval .= $row['suositus_srk_muu'] . '/';
      $retval .= $row['suositus_nimi'];
      if ($fullinfo) {
        $retval .= "\n" . 'Osoite: ' . "\n" . $row['suositus_osoite'] . "\n";
        $retval .= $row['suositus_postinro'] . ' ' . $row['suositus_kaupunki'] . "\n";
        $retval .= 'Puhelin: ' . $row['suositus_puhelin'] . "\n";
        $retval .= 'Email: ' . $row['suositus_email'] . "\n";
      }
    }
  }

  $db->Close();

  return $retval;
}

function GetPlaceName($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT nimi FROM majoituskohteet WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('nimi');
  else
    $nimi = "";

  $db->Close();

  return $nimi;
}

function GetPlaceIdByRoomId($id)
{
  if ($id == 0 || $id == '')
    return 0;

  $db = new CDb;
  $db->Connect();
  $db->Query('SELECT majoituskohde_id FROM majoitushuoneet WHERE id=' . $id . ' LIMIT 1');

  if ($db->NumRows() > 0) {
    $kohde_id = $db->GetResultField('majoituskohde_id');
  } else {
    $kohde_id = 0;
  }

  $db->Close();

  return $kohde_id;
}

function GetAccomodationNameByRoomId($id, $onlyplace = false, $onlyroom = false)
{
  if ($id == 0 || $id == "")
    return "{ei majoituskohdetta}";

  $kohde_nimi = '';

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT nimi, majoituskohde_id FROM majoitushuoneet WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    $nimi = $db->GetResultField('nimi');
    $kohde_id = $db->GetResultField('majoituskohde_id');
  } else {
    $nimi = "{ei asetettu, id:$id}";
    $kohde_id = 0;
  }

  if ($kohde_id > 0) {
    $db->Query("SELECT nimi FROM majoituskohteet WHERE id='" . $kohde_id . "' LIMIT 1");
    if ($db->NumRows() > 0)
      $kohde_nimi = $db->GetResultField('nimi');
    else
      $kohde_nimi = "{ei asetettu, id:$id}";
  }

  $db->Close();

  if ($onlyplace)
    return $kohde_nimi;

  if ($onlyroom)
    return $nimi;

  return ($kohde_nimi . ', huone: ' . $nimi);
}

function GetAccomodationGroupByRoomId($id, $onlyplace = false, $onlyroom = false)
{
  if ($id == 0 || $id == "")
    return "{ei majoituskohdetta}";

  $ryhma = '';
  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT majoituskohde_id FROM majoitushuoneet WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    $kohde_id = $db->GetResultField('majoituskohde_id');
  } else {
    $kohde_id = 0;
  }

  if ($kohde_id > 0) {
    $db->Query("SELECT ryhma FROM majoituskohteet WHERE id='" . $kohde_id . "' LIMIT 1");
    if ($db->NumRows() > 0)
      $ryhma = $db->GetResultField('ryhma');
    else
      $ryhma = "{ei asetettu, id:$id}";
  }

  $db->Close();

  return $ryhma;
}

function GetKansliaJakeluByRoomId($id)
{
  if ($id == 0 || $id == "")
    return "0";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT kanslia_jakelu FROM majoitushuoneet LEFT JOIN majoituskohteet ON majoituskohteet.id=majoituskohde_id WHERE majoitushuoneet.id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    $kj = $db->GetResultField('kanslia_jakelu');
  } else {
    $kj = 0;
  }

  $db->Close();

  return $kj;
}

function GetWorkUnitById($id)
{
  if ($id == 0 || $id == "")
    return "{ei ty&ouml;pistett&auml;}";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT nimi FROM tyopisteet WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('nimi');
  else
    $nimi = "{ei asetettu, id:$id}";

  $db->Close();

  return $nimi;
}

// for old time sake - ei jaksa siis korjata...
function GetWorkPlaceById($id)
{
  return GetWorkUnitById($id);
}

function GetCardTypeById($id)
{
  if ($id == 0 || $id == "")
    return "ei korttia";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT kortti FROM tyot WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $kortti = $db->GetResultField('kortti');
  else
    $kortti = "ei korttia";

  $db->Close();

  return $kortti;
}

function GetWorkTitleById($id)
{
  if ($id == 0 || $id == "")
    return "{ei teht&auml;v&auml;&auml;}";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT nimi FROM tehtavanimikkeet WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    $nimi = $db->GetResultField('nimi');
  } else
    $nimi = "{ei asetettu}";

  $db->Close();

  return $nimi;
}

function GetInvitationByWorkUnitId($id)
{
  global $S;

  $sql = 'SELECT henkilot.id AS hloid, henkilot.*, tyopisteet.nimi AS tyopiste, seurakunnat.nimi AS seurakunta ';
  $sql .= 'FROM tyot LEFT JOIN henkilot ON henkilo_id=henkilot.id ';
  $sql .= 'LEFT JOIN tyopisteet ON tyopisteet.id=tyot.tyopiste_id ';
  $sql .= 'LEFT JOIN seurakunnat ON henkilot.seurakunta_id=seurakunnat.id ';
  $sql .= 'WHERE vuosi=' . ($S->Year() - 1) . ' ';
  $sql .= 'AND (tyot.palaute=\'kutsutaan\' OR tyot.palaute=\'ei asetettu\' OR tyot.palaute=\'erityisainesta\' ';
  //  $sql .= 'OR tyot.palaute=\'\' OR tyot.palaute IS NULL ';
  $sql .= ') ';
  $sql .= 'AND (tyot.asema=\'tyontekija\' OR tyot.asema=\'luottohenkilo\') ';
  //$sql .= 'AND tyopisteet.eikutsua != 1 ';
  if ($id > 0)
    $sql .= 'AND tyopisteet.id=' . $id . ' ';
  $sql .= 'GROUP BY henkilot.id '; // prevent duplicate logins for persons with two or more works...
  $sql .= 'ORDER BY sukunimi,etunimi';

  // REMOVE !!!
  //$sql .= ' LIMIT 15 ';

  //  $db->Close();
  // DB close must be done on caller function!!

  return $sql;
}


function GetBROKENInvitationByWorkUnitId($id)
{
  global $S;

  $sql = 'SELECT henkilot.id AS hloid, henkilot.*, tyopisteet.nimi AS tyopiste, seurakunnat.nimi AS seurakunta ';
  $sql .= 'FROM tyot LEFT JOIN henkilot ON henkilo_id=henkilot.id ';
  $sql .= 'LEFT JOIN tyopisteet ON tyopisteet.id=tyot.tyopiste_id ';
  $sql .= 'LEFT JOIN seurakunnat ON henkilot.seurakunta_id=seurakunnat.id ';
  $sql .= 'WHERE vuosi=' . ($S->Year() - 1) . ' ';
  $sql .= 'AND (tyot.palaute=\'\' OR tyot.palaute IS NULL ) ';
  $sql .= 'AND (tyot.asema=\'tyontekija\' OR tyot.asema=\'luottohenkilo\') ';
  $sql .= 'AND tyopisteet.eikutsua != 1 ';
  if ($id > 0)
    $sql .= 'AND tyopisteet.id=' . $id . ' ';
  $sql .= 'GROUP BY henkilot.id'; // prevent duplicate logins for persons with two or more works...

  // REMOVE !!!
  //$sql .= ' LIMIT 15 ';

  //  $db->Close();
  // DB close must be done on caller function!!

  return $sql;
}

function CreateLoginNameAndPassword($id)
{
  if ($id == 0 || $id == "")
    return "";

  $tunnus = '';
  $sala = '';
  $db = new CDb;
  $db->Connect();

  $db->Query("SELECT etunimi, sukunimi FROM henkilot WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    $etu = explode(' ', $db->GetResultField('etunimi'));
    $etu = $etu[0];

    $etunimi = myscandstoasc(mylower($etu));
    $sukunimi = myscandstoasc(mylower($db->GetResultField('sukunimi')));
    $tunnus = $etunimi . "." . $sukunimi;

    // Tarkista onko tunnus jo olemassa
    $db = new CDb;
    $db->Connect();

    $db->Query('SELECT * FROM login WHERE henkilo_id=' . $id);
    if ($db->NumRows() > 0) {
      // henkilolla on jo tunnus
      $tunnus = $db->GetResultField('tunnus');
      print "henkilolla on jo tunnus: $tunnus<br>";
    } else {
      $i = 2;
      // tarkista onko tunnuksella jo joku olemassa, saman niminen
      $db->Query('SELECT * FROM login WHERE tunnus=\'' . $tunnus . '\'');
      while ($db->NumRows() > 0) {
        // tunnus on jo kaytossa jollakulla muulla
        print "tunnus:" . $tunnus . " on jo kaytossa<br>";
        $tunnus = $etunimi . "." . $i . "." . $sukunimi;
        $db->Query('SELECT * FROM login WHERE tunnus=\'' . $tunnus . '\'');
        $i++;
      }
    }
    $random = make_random(10, 99);
    $sala = substr($etunimi, 0, 2) . substr($sukunimi, 0, 2) . $random;
  }

  $db->Close();

  return array($tunnus, $sala);
}

function CreateLoginAccount($user_id, $login, $password, $kutsussa = false, $group = 'tyontekija', $store_plaintext = false)
{
  if ($user_id == 0 || $user_id == "") {
    print "cant create login, while user_id == 0<br>";
    print "login: $login<br>";
    return "";
  }
  $db = new CDb;
  $db->Connect();

  $db->Query('SELECT id FROM login WHERE henkilo_id=' . $user_id);
  if ($db->NumRows() > 0) {
    //    return 0; // do not create new one!!
    $id = $db->GetResultField('id');
  }
  // luo login tunnus
  $l = array();
  $l['henkilo_id'] = $user_id;
  $l['tunnus'] = $login;
  $l['salasana'] = "PASSWORD('" . $password . "')";
  $l['ryhma'] = $group;
  $l['kutsussa'] = ($kutsussa == true ? 1 : 0);
  if ($store_plaintext == true)
    $l['plain_text'] = $password;

  if ($id > 0)
    $l['id'] = $id;

  $db->Table = 'login';
  if ($id > 0)
    $db->Update($l);
  else {
    $new_id = $db->Insert($l);
    $id = $new_id;
  }

  $db->Close();

  return $id;
}

function GetPersonNameById($id, $kaupunki = false, $firstfirst = false, $allnames = false)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();

  $db->Query("SELECT etunimi,kutsumanimi, sukunimi, kaupunki FROM henkilot WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    if (strlen($db->GetResultField('kutsumanimi')) > 0)
      $etu = $db->GetResultField('kutsumanimi');
    else {
      if ($allnames == false) {
        $etu = explode(' ', $db->GetResultField('etunimi'));
        $etu = $etu[0];
      } else {
        $etu = $db->GetResultField('etunimi');
      }
    }
    $etu = finecase($etu);
    $suku = finecase($db->GetResultField('sukunimi'));
    $kau = finecase($db->GetResultField('kaupunki'));
  } else {
    $etu = "";
    $suku = "[ei l&ouml;ytynyt, id:$id]";
  }


  if ($firstfirst)
    $retval = $etu . ' ' . $suku;
  else
    $retval = $suku . ' ' . $etu;

  if ($kaupunki)
    $retval .= ', ' . $kau;

  $db->Close();

  return $retval;
}

function GetFullNameById($id, $kaupunki = false)
{
  return GetPersonNameById($id, $kaupunki, false, true);
  /*  
  if ($id == 0 || $id == "")
      return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT etunimi,kutsumanimi, sukunimi FROM henkilot WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    {
      if (strlen($db->GetResultField('kutsumanimi')) > 0)
	$etu = $db->GetResultField('kutsumanimi');
      else
	$etu = explode(' ',$db->GetResultField('etunimi'));
      $suku = $db->GetResultField('sukunimi');
    }
  else
    {
      $etu = "";
      $suku = "[ei l&ouml;ytynyt, id:$id]";
    }
  $db->Close();

  return ($etu . " " . $suku);
    */
}

function GetPersonGroupNameById($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT ryhma FROM login WHERE henkilo_id='" . $id . "' LIMIT 1");

  if ($db->NumRows() < 1)
    $nimi = "ei ryhm&auml;&auml;";
  else
    $nimi = $db->GetResultField('ryhma');

  $db->Close();

  return $nimi;
}

function GetHenkiloTyotAndTehtavaIdById($hloid, $tpid)
{
  global $S;

  if ($hloid == 0 || $hloid == "")
    return "";

  $vuosi = $S->GetVar('year');

  $db = new CDb;
  $db->Connect();

  $db->Query("SELECT id,tehtavanimike_id FROM tyot WHERE henkilo_id='" . $hloid . "' AND tyopiste_id='" . $tpid . "' AND vuosi=$vuosi LIMIT 1");

  $tnid = $db->GetResultField('tehtavanimike_id');
  $tyotid = $db->GetResultField('id');

  $db->Close();

  return array($tyotid, $tnid);
}

function GetHenkiloTyotIdById($hloid, $wish = false)
{
  global $S;

  if ($hloid == 0 || $hloid == "")
    return "";

  $vuosi = $S->GetVar('year');

  $db = new CDb;
  $db->Connect();

  $db->Query("SELECT id,tyopiste_id,tehtavanimike_id,toive1,toive2,toive3 FROM tyot WHERE henkilo_id='" . $hloid . "' AND vuosi=$vuosi LIMIT 1");

  if ($db->NumRows() > 0) {
    $tnid = $db->GetResultField('tehtavanimike_id');
    $tyotid = $db->GetResultField('id');
    if ($wish) {
      $tpid1 = $db->GetResultField('toive1');
      $tpid2 = $db->GetResultField('toive2');
      $tpid3 = $db->GetResultField('toive3');
    } else
      $tpid = $db->GetResultField('tyopiste_id');
  } else {
    $tnid = 0;
    $tyotid = 0;
    $tpid = 0;
  }
  $db->Close();

  if ($wish)
    $arr = array($tyotid, $tpid1, $tpid2, $tpid3);
  else
    $arr = array($tyotid, $tpid, $tnid);
  return $arr;
}

function GetHenkiloPalauteById($hloid, $tpid)
{
  global $S;

  if ($hloid == 0 || $hloid == "")
    return "";

  $vuosi = $S->GetVar('year');

  $db = new CDb;
  $db->Connect();

  $db->Query("SELECT palaute FROM tyot WHERE henkilo_id='" . $hloid . "' AND tyopiste_id='" . $tpid . "' AND vuosi=$vuosi LIMIT 1");

  $palaute = $db->GetResultField('palaute');

  $db->Close();

  return $palaute;
}

function GetHenkiloState($hloid, $tpid)
{
  global $S;

  if ($hloid == 0 || $hloid == "")
    return "";

  $vuosi = $S->GetVar('year');

  $db = new CDb;
  $db->Connect();

  $db->Query("SELECT asema FROM tyot WHERE henkilo_id='" . $hloid . "' AND tyopiste_id='" . $tpid . "' AND vuosi=$vuosi LIMIT 1");

  $asema = $db->GetResultField('asema');

  $db->Close();

  return $asema;
}

function GetHenkiloCardText($hlotyotid)
{
  if ($hlotyotid == 0 || $hlotyotid == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT kortti_teksti FROM tyot WHERE id='" . $hlotyotid . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $kortti = $db->GetResultField('kortti_teksti');
  else
    $kortti = "";

  $db->Close();

  return $kortti;
}

function GetAccomodationGroupNameById($id)
{
  switch ($id) {
    case 'isokirja':
      return 'Iso Kirja';
    case 'yhteismajoitus':
      return 'Yhteismajoitus';
    case 'yhteismajoitus naiset':
      return 'Yhteismajoitus miehet';
    case 'yhteismajoitus miehet':
      return 'Yhteismajoitus naiset';
    case 'lahialueet':
      return 'L&auml;hialueet';
    case 'vuokravaunut':
      return 'Vuokravaunut';
  }

  return '';
}

function GetSeurakuntaById($id, $yhthlo = true)
{
  if ($id == 0 || $id == "")
    return "{ei seurakuntaa}";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT nimi, yhteyshlo_id FROM seurakunnat WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('nimi');
  else
    $nimi = "{ei asetettu, id:$id}";

  if ($yhthlo && $db->GetResultField('yhteyshlo_id') > 0)
    $nimi .= ' (y)';

  $db->Close();

  return $nimi;
}

function GetPhoneById($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT puhelin1 FROM henkilot WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('puhelin1');
  else
    $nimi = "EI";

  $db->Close();

  return $nimi;
}

function GetEmailById($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT email FROM henkilot WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('email');
  else
    $nimi = "EI";

  $db->Close();

  return $nimi;
}

function GetSrkEmailById($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT email, yhteyshlo_id FROM seurakunnat WHERE id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0) {
    $email = $db->GetResultField('email');
    $yhteyshlo_id = $db->GetResultField('yhteyshlo_id');
  }

  if ($email == '')
    $email = GetEmailById($yhteyshlo_id);

  $db->Close();

  return $email;
}

function GetLoginById($id)
{
  if ($id == 0 || $id == "")
    return "";

  $db = new CDb;
  $db->Connect();
  $db->Query("SELECT tunnus FROM login WHERE henkilo_id='" . $id . "' LIMIT 1");

  if ($db->NumRows() > 0)
    $nimi = $db->GetResultField('tunnus');
  else
    $nimi = "";

  $db->Close();

  return $nimi;
}
