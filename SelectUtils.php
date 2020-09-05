<?php

function MakeSelect($array, $name, $selection, $options = '', $print = true)
{
  $retval = '<select name="' . $name . '" size="1" ' . $options . ">\n";

  foreach ($array as $index => $key) {
    // if index is not number, use then them in the value argument
    if (is_numeric($index))
      $value = $key;
    else
      $value = $index;

    if ($value == $selection)
      $retval .= '<option selected value="' . $value . '">' . $key . '</option>' . "\n";
    else
      $retval .= "<option value=\"$value\">$key</option>\n";
  }

  $retval .= "</select>\n\n";

  if ($print)
    print $retval;

  return $retval;
}

function MakeMonthSelect($name, $selection, $options = '', $print = true)
{
  $retval = '<select name="' . $name . '" size="1" ' . $options . ">\n";

  for ($i = 1; $i <= 12; $i++) {
    if ($i == $selection)
      $retval .= '<option selected value="' . $i . '">' . monthofyear($i) . '</option>' . "\n";
    else
      $retval .= "<option value=\"$i\">" . monthofyear($i) . "</option>\n";
  }

  $retval .= "</select>\n\n";

  if ($print)
    print $retval;

  return $retval;
}
