<?php

if (!class_exists('CDb')) { // redefinition protect

  class CDb
  {
    var $Host = NULL;
    var $User = NULL;
    var $Pass = NULL;
    var $Database = NULL;
    var $Table = ''; // no default table
    var $link = NULL;
    var $Result = 0;
    var $Record = 0;
    var $Row = 0;
    var $Config = NULL;

    function GetCurrentDb()
    {
      return $this->Database;
    }

    function Connect()
    {
      if ($this->Config == NULL) {
        $this->Config = parse_ini_file("config.ini");
        $this->Host = $this->Config["Host"];
        $this->User = $this->Config["User"];
        $this->Pass = $this->Config["Pass"];
        $this->Database = $this->Config["Database"];
      }

      if ($this->link == NULL) {
        $this->link = mysqli_connect($this->Host, $this->User, $this->Pass) or
          die('Unable to connect host: ' . $this->Host . ' Error: ' . mysqli_error($this->link));
      }
      if ($this->Database != '') {
        mysqli_select_db($this->link, $this->Database) or
          die('Unable to select database: ' . $this->Database . ' Error: ' . mysqli_error($this->link));
      }
    }

    function Query($sql)
    {
      if ($this->link == NULL)
        $this->Connect();

      // free previous results
      if ($this->Result)
        mysqli_free_result($this->Result);

      $this->Result = mysqli_query($this->link, $sql) or
        die('Error in query "' . $sql . '". ' . mysqli_error($this->link));

      // if we use insert or update clause don't store result
      if (stristr($sql, 'INSERT') != false) $this->Result = 0;
      if (stristr($sql, 'UPDATE') != false) $this->Result = 0;

      //echo 'num rows = ' . mysqli_num_rows($this->Result);

      // Default is to read first row 
      //$this->next_record();
    }

    function Exec($sql)
    {
      if ($this->link == NULL)
        $this->connect();

      // free previous results
      if ($this->Result)
        mysqli_free_result($this->Result);

      mysqli_query($this->link, $sql) or
        die('Error in query "' . $sql . '". ' . mysqli_error($this->link));

      return mysqli_affected_rows($this->link);
    }

    function FetchArray()
    {
      return mysqli_fetch_array($this->Result, MYSQLI_ASSOC);
    }

    function NextRecord()
    {
      // This is fast way to get data, mysqli_result() is slow one
      $this->Record = mysqli_fetch_array($this->Result, MYSQLI_ASSOC) or
        die('Error fetching next record. ' . mysqli_error($this->link) . ' Errno: ' . mysqli_errno($this->link));
      $this->Row   += 1;

      $stat = is_array($this->Record);
      if (!$stat) {
        mysqli_free_result($this->Result);
        $this->Result = 0;
      }
      return $stat;
    }

    function Seek($pos)
    {
      $status = mysqli_data_seek($this->Result, $pos);
      if ($status)
        $this->Row = $pos;
      return;
    }

    function NumRows()
    {
      $count = mysqli_num_rows($this->Result);
      return $count;
    }

    function InsertId()
    {
      $res = mysqli_insert_id($this->link);
      return $res;
    }

    function GetResultField($field)
    {
      $res = mysqli_fetch_assoc($this->Result);
      //or
      //die ('Error getting result for field "' . $field . '". errno: ' . mysqli_errno() . ' Error:' . mysqli_error($this->link));

      return $res[$field];
    }

    function GetRecord($field, $row = -1)
    {
      // locally made
      /*
       // FIXME: check which should be the default row value?
      $res = mysqli_result($this->Result, $row, $field) or
      	die ('Error getting result for field '' . $field . ''. ' . mysqli_error($this->link));
      */
      if ($row != -1)
        $this->seek($row);
      $res = $this->Record[$field];
      return $res;
    }

    function Record($field, $row = -1)
    {
      echo $this->getrecord($field, $row);
    }

    function Close()
    {
      if ($this->Result)
        mysqli_free_result($this->Result);

      //if ($this->link)
      //  mysqli_close($this->link);
      $this->link = NULL;
      $this->Row = 0;
      $this->Result = 0;
      $this->Record = 0;
    }

    function GenerateFieldArray($source)
    {
      $result_values = array();

      foreach ($source as $field => $value) {
        if (substr($field, 0, 2) == 'f_') {
          // field value
          $result_values[substr($field, 2)] = $value;
        }
      }
      return $result_values;
    }

    function Insert($values)
    {
      if ($this->Table == '')
        die('No table selected while instering data');

      if (count($values) == 0)
        die('No values given while instering data');

      $first_row = 1;

      $sql = 'INSERT INTO ' . $this->Table . ' SET';
      foreach ($values as $field => $value) {
        if ($first_row == 1)
          $first_row = 0;
        else
          $sql = $sql . ',';

        if ($value == 'NULL')
          $sql = $sql . ' `' . $field . '`=' . addslashes($value) . '';
        elseif (strpos($value, 'PASSWORD(') !== false)
          $sql = $sql . ' `' . $field . '`=' . $value;
        else
          $sql = $sql . ' `' . $field . '`="' . addslashes($value) . '"';
      }
      //print 'Executing query:<br>';
      //print $sql;

      $retval = mysqli_query($this->link, $sql) or
        die('Error in insert. ' . mysqli_error($this->link) . '<br>Query: ' . $sql);

      $a_rows = mysqli_affected_rows($this->link);
      if ($a_rows != 1)
        die('Insert affected to $a_rows rows!!<br>Query: $sql<br>');

      return $this->InsertId();
    }

    function Update($values, $show_error = false)
    {
      if ($this->Table == '')
        die('No table selected while updating data');

      if (count($values) == 0)
        die('No values given while updating data');

      $first_row = 1;
      $where = ' WHERE ';

      $sql = 'UPDATE ' . $this->Table . ' SET';
      foreach ($values as $field => $value) {
        if ($field == 'id') {
          $where = $where . '`' . $field . '`="' . trim($value) . '"';
          continue;
        }

        if ($first_row == 1)
          $first_row = 0;
        else
          $sql = $sql . ',';

        if ($value == 'NULL')
          $sql = $sql . ' `' . $field . '`=' . addslashes($value) . '';
        elseif (strpos($value, 'PASSWORD(') !== false)
          $sql = $sql . ' `' . $field . '`=' . $value;
        else
          $sql = $sql . ' `' . $field . '`="' . addslashes($value) . '"';
      }

      $sql = $sql . $where;

      //print 'Executing query:<br>';
      //print $sql;

      $retval = mysqli_query($this->link, $sql) or
        die('Error in update. ' . mysqli_error($this->link) . '<br>Query: ' . $sql);

      $a_rows = mysqli_affected_rows($this->link);

      // FIXME: is this error??
      if ($a_rows != 1 && $show_error)
        die('Update affected to $a_rows rows!!<br>Query: $sql<br>');
    }

    function Delete($id)
    {
      if ($this->Table == '')
        die('No table selected while deleting data - nothing deleted');

      if ($id == NULL)
        die('No values given while deleting data - nothing deleted');

      $sql = 'DELETE FROM ' . $this->Table . ' WHERE `id`="' . $id . '"';

      $retval = mysqli_query($this->link, $sql) or
        die('Error in delete. ' . mysqli_error($this->link) . '<br>Query: ' . $sql);

      $a_rows = mysqli_affected_rows($this->link);

      if ($a_rows != 1)
        die('Delete affected to $a_rows rows!!<br>Query: $sql<br>');
    }
  }
} // redefinition protect
